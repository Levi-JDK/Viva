# 🔐 Almacenamiento Biométrico en PostgreSQL

> **Regla de Oro:** Nunca guardes la imagen cruda. Guardá el *template* transformado.

---

## 🧠 Conceptos Clave

| Concepto | Descripción |
|----------|-------------|
| **Template** | Vector numérico de características extraídas de la cara o huella. |
| **Cancelable Biometrics** | Transformación irreversible que permite "revocar" un template sin cambiar tu cara. |
| **Similitud** | El matching no es exacto (`=`), es por proximidad (coseno o euclídea). |

---

## 🛠️ 1. Instalación de `pgvector`

```bash
# En Debian/Ubuntu
sudo apt install postgresql-16-pgvector

# En la base de datos
psql -d tu_base -c "CREATE EXTENSION IF NOT EXISTS vector;"
```

---

## 🗄️ 2. Esquema de Base de Datos

Separá la biometría del resto. **Nunca** juntes `users` con `biometric_templates`.

```sql
CREATE EXTENSION IF NOT EXISTS vector;

-- ============================================
-- Tabla de usuarios (dominio público)
-- ============================================
CREATE TABLE users (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    email text NOT NULL,
    created_at timestamptz DEFAULT now()
);

-- ============================================
-- Secrets para Cancelable Biometrics
-- ============================================
CREATE TABLE biometric_secrets (
    user_id uuid PRIMARY KEY REFERENCES users(id),
    secret bytea NOT NULL, -- Aleatorio por usuario
    created_at timestamptz DEFAULT now()
);

-- ============================================
-- Templates transformados (dominio sensible)
-- ============================================
CREATE TABLE biometric_templates (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id uuid NOT NULL REFERENCES users(id),
    modality text NOT NULL CHECK (modality IN ('face', 'fingerprint')),
    template vector(512) NOT NULL, -- Dimensión de tu extractor
    created_at timestamptz DEFAULT now()
);

-- ============================================
-- Índice para búsqueda por similitud
-- ============================================
CREATE INDEX idx_bio_templates_face
ON biometric_templates
USING ivfflat (template vector_cosine_ops)
WITH (lists = 100);
```

---

## 🔄 3. Flujo Completo de Almacenamiento

```
┌─────────────┐
│  📷 Captura │  <-- Cámara o lector de huellas
└──────┬──────┘
       │
       ▼
┌──────────────┐
│  🖼️ Imagen   │  <-- Vive solo en memoria RAM
│     RAW      │
└──────┬──────┘
       │
       ▼
┌──────────────┐
│  🧮 Extraer  │  <-- FaceNet, ArcFace, SDK del lector
│   Template   │     Output: vector de 512 floats
└──────┬──────┘
       │
       ▼
┌──────────────┐
│  🔑 Secret   │  <-- Aleatorio único por usuario
│   del User   │
└──────┬──────┘
       │
       ▼
┌──────────────┐
│  🛡️ BioHash  │  <-- Transformación irreversible
│  (Transform) │
└──────┬──────┘
       │
       ▼
┌──────────────┐
│  💾 INSERT   │  <-- PostgreSQL + pgvector
│   Postgres   │
└──────────────┘
```

---

## 🐍 4. Código Python: Transformación Cancelable

```python
import numpy as np
import hashlib

def biohash(embedding: np.ndarray, secret: bytes, length: int = 512) -> np.ndarray:
    """
    Aplica una proyección aleatoria determinística al embedding.
    Sin el 'secret', el vector transformado es inútil.
    """
    # Semilla determinística desde el secret
    seed = int(hashlib.sha256(secret).hexdigest(), 16) % (2**32)
    rng = np.random.RandomState(seed)
    
    # Matriz de proyección
    projection = rng.randn(len(embedding), length)
    
    # Transformar y binarizar
    transformed = np.dot(embedding, projection)
    return np.sign(transformed)  # Vector de -1 y 1
```

---

## 🐍 5. Código Python: Registro de Nuevo Usuario

```python
import psycopg2
import numpy as np
import os

def register_user(email: str, captured_image) -> str:
    """
    1. Extrae template
    2. Genera secret
    3. Aplica BioHash
    4. Guarda en Postgres
    """
    # Paso 1: Extraer embedding (ej: FaceNet)
    embedding = extract_template(captured_image)  # Tu función
    
    # Paso 2: Generar secret aleatorio
    secret = os.urandom(32)
    
    # Paso 3: Transformar
    transformed = biohash(embedding, secret)
    
    # Paso 4: Guardar en DB
    conn = psycopg2.connect("dbname=viva user=postgres")
    cur = conn.cursor()
    
    # Insertar usuario
    cur.execute(
        "INSERT INTO users (email) VALUES (%s) RETURNING id",
        (email,)
    )
    user_id = cur.fetchone()[0]
    
    # Guardar secret
    cur.execute(
        "INSERT INTO biometric_secrets (user_id, secret) VALUES (%s, %s)",
        (user_id, secret)
    )
    
    # Guardar template transformado
    # Convertir [-1, 1] a lista de floats para pgvector
    template_list = transformed.tolist()
    cur.execute(
        "INSERT INTO biometric_templates (user_id, modality, template) VALUES (%s, %s, %s)",
        (user_id, 'face', template_list)
    )
    
    conn.commit()
    return user_id
```

---

## 🔍 6. Código Python: Verificación (1:1)

```python
def verify_user(user_id: str, captured_image) -> bool:
    """
    Compara una nueva captura contra el template almacenado del usuario.
    """
    # Extraer nuevo embedding
    new_embedding = extract_template(captured_image)
    
    conn = psycopg2.connect("dbname=viva user=postgres")
    cur = conn.cursor()
    
    # Obtener secret del usuario
    cur.execute("SELECT secret FROM biometric_secrets WHERE user_id = %s", (user_id,))
    secret = cur.fetchone()[0]
    
    # Transformar la nueva captura con el MISMO secret
    new_transformed = biohash(new_embedding, secret)
    
    # Obtener template almacenado
    cur.execute(
        "SELECT template FROM biometric_templates WHERE user_id = %s",
        (user_id,)
    )
    stored_template = np.array(cur.fetchone()[0])
    
    # Calcular similitud coseno
    similarity = np.dot(new_transformed, stored_template) / (
        np.linalg.norm(new_transformed) * np.linalg.norm(stored_template)
    )
    
    # Umbral típico: 0.80 - 0.90
    return similarity > 0.85
```

---

## 🌍 7. Identificación (1:N) con PostgreSQL

```sql
-- Encontrar los 5 usuarios más similares
SELECT 
    user_id, 
    1 - (template <=> $1::vector) AS similarity
FROM biometric_templates
WHERE modality = 'face'
ORDER BY template <=> $1::vector
LIMIT 5;
```

```python
def identify_user(captured_image) -> list:
    """
    Busca quién es la persona entre todos los registrados.
    NOTA: Cancelable biometrics complica el 1:N. 
          Para 1:N puro, se suele usar HSM o no transformar.
    """
    new_embedding = extract_template(captured_image)
    
    # Si NO usás cancelable para 1:N:
    # template_list = new_embedding.tolist()
    
    # Si SÍ usás cancelable, necesitás transformar con CADA secret (O(N) costoso)
    # Por eso muchos sistemas usan HSM para 1:N
    
    conn = psycopg2.connect("dbname=viva user=postgres")
    cur = conn.cursor()
    
    # Ejemplo sin cancelable (matching directo en pgvector)
    template_list = new_embedding.tolist()
    cur.execute("""
        SELECT user_id, 1 - (template <=> %s::vector) AS similarity
        FROM biometric_templates
        WHERE modality = 'face'
        ORDER BY template <=> %s::vector
        LIMIT 5
    """, (template_list, template_list))
    
    return cur.fetchall()  # [(user_id, similarity), ...]
```

---

## 🛡️ 8. Seguridad: Checklist

| Capa | Protección | Implementación |
|------|-----------|----------------|
| 📷 **Captura** | Liveness detection, TLS 1.3 | Evitar fotos de fotos |
| 🧠 **Extracción** | TEE / Edge device | FaceNet en móvil, no en servidor |
| 🔑 **Secretos** | HSM o tabla aislada | Nunca junto a templates |
| 🚚 **Transporte** | mTLS / TLS 1.3 | Entre microservicios |
| 💾 **Postgres** | pgvector + IDs opacos | Sin emails ni nombres en tabla bio |
| 🔒 **Disco** | TDE o LUKS | Encriptación transparente |
| 👤 **Acceso** | RLS + Roles separados | `bio_reader`, `bio_writer` |

---

## ⚠️ 9. Advertencias Importantes

1. **No almacenar imágenes crudas.** Extraé el template y descartá la imagen inmediatamente.
2. **Cancelable Biometrics complica el 1:N.** Para identificación masiva, evaluá HSM o matching en hardware seguro.
3. **Nunca reutilices secrets.** Cada usuario tiene su propio `user_secret`.
4. **GDPR / LGPD.** Los datos biométricos son sensibles. Necesitás base legal explícita para procesarlos.

---

## 📚 Referencias

- [pgvector GitHub](https://github.com/pgvector/pgvector)
- ISO/IEC 19794-2 (Huellas)
- ISO/IEC 19794-5 (Rostros)
- NIST SP 800-76-2 (Biometrics)

---

*Documento generado para el proyecto VIVA.*
