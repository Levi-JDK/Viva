# Sistema de Validación IA — VIVA Marketplace

## Arquitectura General

```
[Usuario] → Sube producto → upload_product.php (síncrono, rápido)
                                │
                                ▼
                          Redis Queue (viva:cola:validacion)
                                │
                                ▼
              ┌─────────────────────────────────────────────┐
              │         ValidationWorker (CLI)              │
              │  supervisord: viva-validation-worker        │
              │                                             │
              │  1. processTempImages() → move img + vars   │
              │  2. hashImages() → sha256 + phash + dhash   │
              │  3. processImageEmbeddings() → IA embed     │
              │  4. embedAndSaveProductData() → texto       │
              │  5. Threshold check (rag.min_examples)      │
              │  6. applyDecisionModel() → IA decide        │
              └─────────────────────────────────────────────┘
```

## Flujo de Validación (Paso a Paso)

### 1. Subida del Producto (`upload_product.php`)
- Valida datos del formulario
- Crea producto DB vía `fun_c_producto()` con `validation_status = 'pending_images'`
- Guarda imagen RAW en `images/products/temp/` (SIN procesar)
- Registra path temporal en `tab_imagenes`
- Encola a Redis: `viva:cola:validacion`
- **Responde al cliente inmediatamente** ✅

### 2. Worker de Validación (`ValidationWorker.php`)
Loop infinito manejado por **supervisord**:
```bash
supervisorctl status viva-validation-worker
```
Procesa mensajes de Redis (`brpop`), con try/catch en loop principal para no morir.

### 3. Procesamiento de Imágenes (`ProductValidationJob::processTempImages()`)
- Mueve imagen de `temp/` → `images/products/`
- Genera variantes vía `generateVariants()` (thumb, medium, full + WebP)
- Actualiza `tab_imagenes.url_imagen` con path final
- Recarga paths desde DB con URL pública (`AI_PUBLIC_URL`)
- Cambia status a `pending_review`

### 4. Hashing de Imágenes (`HashService`)
- **file_hash**: SHA-256 del archivo (detección de imagen exacta)
- **phash**: Perceptual hash (64 bits) para imágenes similares
- **dhash**: Difference hash (64 bits) para variantes

### 5. Embeddings de Imagen (`AIProviderRouter::generateImageEmbedding`)
- Toma la URL pública de la imagen
- La envía al proveedor IA (NVIDIA OpenRouter como fallback)
- Modelo: `nvidia/llama-nemotron-embed-vl-1b-v2`
- Guarda en `ai.product_image_embeddings`
- Busca productos visualmente similares (similitud ≥ 0.90)

### 6. Embedding de Texto Combinado (`TextEmbeddingService::embedAndSaveProductData`)
- Combina **título + descripción** en un solo texto
- Formato: `"Sombrero Suaceño. Sombrero artesanal hecho a mano con fibra de iraca..."`
- Genera embedding de 2048 dimensiones vía IA
- Guarda en `ai.product_text_embeddings`
- **NO incluye**: materiales, categoría (solo título + descripción)

### 7. Coherencia Texto-Imagen
- Se calcula con **solo el título** (no el texto combinado completo)
- Genera embedding fresco del título vía `computeTextImageCoherence()`
- Compara con embeddings de imagen vía similitud coseno
- Umbrales: `≥ 0.80 = alta`, `≥ 0.50 = media`, `< 0.50 = baja`
- Ambos embeddings usan `input_type: 'passage'` (mismo espacio vectorial)

### 8. Búsqueda de Productos Similares
- Busca otros productos con embedding de texto similar
- Excluye productos del mismo productor
- Toma los 5 más similares
- Consulta `product_validation_results` para ver sus decisiones
- Esas decisiones se pasan como contexto a la IA (auto-learning)

### 9. Decisión IA (`applyDecisionModel`)
Recibe como contexto:
- Datos del producto (título, descripción, categoría, materiales)
- Reglas RAG (`ai.rag_rules`)
- Decisiones de productos similares
- Coherencia texto-imagen
- Resultados de plagio visual
- **NO recibe la imagen directamente** (solo el score de coherencia)

La IA responde con JSON:
```json
{
  "decision": "approved|rejected|revision_humana",
  "artesanalidad": {"status": "artesanal|dudosa|no_artesanal", "score": 0.0-1.0, "reason": "..."},
  "motivo_general": "Explicación breve (máx 3 oraciones)"
}
```

### 10. Post-Decisión
- Worker actualiza `tab_productos.validation_status` e `is_active`
- Guarda resultado en `ai.product_validation_results`
- Si admin aprueba/rechaza: `fun_admin_approve_product()` actualiza el registro existente

---

## Tablas en Schema `ai`

### `product_image_embeddings`
Embeddings visuales de imágenes de productos.
- `id_producto`, `id_imagen` → FK a `tab_imagenes`
- `visual_embedding` → VECTOR(2048)
- Usado para buscar imágenes similares visualmente

### `product_text_embeddings`
Embedding de texto combinado (título + descripción).
- 1 fila por producto
- `text_embedding` → VECTOR(2048)
- Usado para búsqueda semántica de productos similares

### `product_validation_results`
Historial de validaciones.
- Cada validación del worker o admin crea/actualiza 1 registro
- `decision`: approved, rejected, revision_humana, pending_validacion_ia, pending_review
- `text_image_status`: alta, media, baja, no_evaluada
- `artisan_status`: artesanal, dudosa, no_artesanal, no_evaluada
- `matched_*`: datos del producto con el que matcheó por plagio

### `rag_rules`
Reglas fijas para contexto RAG (5 reglas).
- `artisan_policy`: 3 reglas (qué es artesanal)
- `plagiarism_policy`: 2 reglas (qué es plagio)
- **Sin embeddings**, solo texto plano

### `config`
Configuración parametrizable desde DB.
- `rag.min_examples`: Mínimo ejemplos antes de que la IA decida sola (default: 20)
- `images.processing_timeout_minutes`: Timeout para orphan cleanup (default: 60)

---

## Archivos del Sistema

### Backend (PHP)

| Archivo | Rol |
|---------|-----|
| `src/workers/ValidationWorker.php` | Loop principal, consume Redis, nunca muere (try/catch) |
| `src/workers/Jobs/ProductValidationJob.php` | Orquesta la validación completa de un producto |
| `src/services/ProductValidationService.php` | Lógica de validación: hashes, embeddings, coherencia, decisión |
| `src/services/TextEmbeddingService.php` | Embeddings de texto, búsqueda semántica, coherencia |
| `src/services/ImageSignatureService.php` | Hashes perceptuales, búsqueda unificada |
| `src/services/HashService.php` | SHA-256 y hashes perceptuales de imágenes |
| `src/services/AIProviderRouter.php` | Router a proveedores IA (OpenRouter + NVIDIA) con failover |
| `src/functions/upload_product.php` | Subida de producto (ahora async, sin procesar imágenes) |
| `src/functions/product_validation_queue.php` | Enqueue a Redis |
| `src/functions/queries.php` | TODAS las consultas SQL del proyecto |
| `src/api/admin_validation_action.php` | Acciones admin (approve/reject/reprocess) |
| `src/api/admin_validation_list.php` | Listado de productos por estado de validación |
| `src/api/validation_status.php` | Estado de validación de un producto específico |

### Frontend (JS)

| Archivo | Rol |
|---------|-----|
| `src/scripts/controllers/AdminValidationController.js` | Panel de validaciones admin |
| `src/scripts/controllers/AdminDashboardController.js` | Dashboard admin con lista de productos |
| `src/scripts/services/ApiService.js` | Redirect automático al login si 401 |
| `src/scripts/services/AdminValidationService.js` | API calls para validaciones |

### Base de Datos

| Archivo | Rol |
|---------|-----|
| `sql/ai/001_ddl.sql` | DDL completo del schema ai (ejecutar con `psql`) |
| `sql/ai/002_functions.sql` | Funciones PL/pgSQL del schema ai (ejecutar con `psql`) |

---

## Proveedores IA

### Configuración (`.env`)
```
AI_PRIMARY_PROVIDER=openrouter
AI_SECONDARY_PROVIDER=nvidia
AI_EMBEDDING_MODEL=nvidia/llama-nemotron-embed-vl-1b-v2
AI_DECISION_MODEL=nvidia/nemotron-3-nano-omni-30b-a3b-reasoning
OPENROUTER_API_KEY=sk-or-...
NVIDIA_API_KEY=nvapi-...
AI_PUBLIC_URL=http://135.119.114.214/viva
```

### Embeddings (texto + imagen)
- **Modelo**: `nvidia/llama-nemotron-embed-vl-1b-v2`
- **Dimensiones**: 2048
- **Input type**: `passage` (mismo espacio vectorial para texto e imagen)
- **Failover**: NVIDIA → OpenRouter

### Decisión
- **Modelo**: `nvidia/nemotron-3-nano-omni-30b-a3b-reasoning`
- **Orden**: NVIDIA → OpenRouter
- **Max tokens**: 1500
- **Temperature**: 0.1

### Endpoints
- Chat: `https://integrate.api.nvidia.com/v1/chat/completions`
- Embeddings: `https://integrate.api.nvidia.com/v1/embeddings`
- OpenRouter Chat: `https://openrouter.ai/api/v1/chat/completions`
- OpenRouter Embed: `https://openrouter.ai/api/v1/embeddings`

---

## Conceptos Clave

### ¿Qué es un embedding?
Un vector de 2048 números que representa el "significado" de un texto o imagen. Productos similares tienen vectores cercanos (similitud coseno alta).

### ¿Qué es la similitud coseno?
Medida de qué tan similares son dos vectores (0 = opuestos, 1 = idénticos). Se usa para comparar embeddings de texto contra imagen (coherencia) y para buscar productos similares.

### ¿Qué es input_type: passage?
Parámetro de NVIDIA que define el "espacio vectorial". Texto e imagen deben usar el MISMO input_type para ser comparables. Siempre `passage` para ambos.

### ¿Qué es auto-learning?
Cuando un admin aprueba/rechaza un producto, la decisión se guarda en `product_validation_results`. El próximo producto similar encontrará esa decisión vía búsqueda semántica y la usará como contexto.

### ¿Qué son las reglas RAG?
5 reglas de texto plano en `ai.rag_rules` que se inyectan como contexto al modelo de decisión. Definen políticas de artesanalidad y plagio. No tienen embeddings.

---

## Mantenimiento

### Worker
```bash
sudo supervisorctl status viva-validation-worker
sudo supervisorctl restart viva-validation-worker
```

### Redis
```bash
redis-cli ping
redis-cli LLEN viva:cola:validacion  # Tamaño de cola
redis-cli LLEN viva:cola:deadletter   # Mensajes fallidos
```

### Logs
```bash
tail -f /tmp/viva-validation-worker.log
tail -f /var/log/apache2/error.log | grep "upload_product\|ProductValidation"
```

### Consultas Útiles
```sql
-- Ver ejemplos acumulados (para threshold)
SELECT COUNT(DISTINCT product_id) FROM ai.product_validation_results WHERE decision IN ('approved', 'rejected');

-- Ver productos pendientes
SELECT p.id_producto, p.nom_producto, p.validation_status
FROM tab_productos p WHERE p.validation_status = 'pending_review';

-- Ver DLQ en Redis
redis-cli LRANGE viva:cola:deadletter 0 -1
```

### Re-procesar un producto
Si un producto quedó trabado:
```bash
php -r "
require_once '/var/www/html/viva/src/functions/database.php';
require_once '/var/www/html/viva/src/functions/product_validation_queue.php';
viva_reenqueue_product_validation(ID_PRODUCTO);
"
```
