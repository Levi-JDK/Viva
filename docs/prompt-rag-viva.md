# Prompt para generar diagrama del RAG Flow de VIVA Marketplace

---

Generá un diagrama de arquitectura/isométrico que muestre el flujo RAG (Retrieval-Augmented Generation) del sistema de validación IA de productos artesanales de VIVA Marketplace.

## Estilo visual
- Diagrama de arquitectura limpio, tipo bloc diagrama con flechas direccionales
- Esquema de colores: azul marino para fondos de componentes principales, verde para bases de datos, naranja para colas/Redis, violeta para servicios IA
- Texto legible, español
- Formato ideal: infografía técnica o diagrama de flujo vertical descendente

## Componentes

### 1. Entrada (Redis Queue)
- **Cola**: `viva:cola:validacion` (Redis)
- **Contenido del mensaje**: `{ product_id, producer_id, productData: { title, description, images, materials, category } }`
- Representar como un círculo/rombo con ícono de cola

### 2. Worker de Validación (`ValidationWorker`)
- Proceso PHP en loop que escucha `brpop` de Redis
- Orquestador del flujo completo
- Timeout: 90s por llamada a provider

### 3. Generación de Embedding de Texto
- **Texto fuente**: `{title} {description}` del producto
- **Provider**: OpenRouter (primario) → NVIDIA (fallback)
- **Modelo**: `nvidia/llama-nemotron-embed-vl-1b-v2`
- **Output**: Vector 2048-d
- **Destino**: `ai.product_text_embeddings` (PostgreSQL con pgvector)
- **Comportamiento**: UPSERT por `product_id`

### 4. Descripción Visual (Modelo Multimodal 30B)
- **Provider**: NVIDIA directamente
- **Modelo**: `nvidia/nemotron-3-nano-omni-30b-a3b-reasoning`
- **Input**: Imagen del producto (enviada como content-type `image_url`) + prompt en español
- **Prompt**: "Eres un experto en artesanías colombianas... Analiza la imagen... Responde JSON con descripcion_semantica"
- **Output JSON**: `{ categoria_visual, producto_probable, descripcion_semantica, etiquetas, confianza }`
- La `descripcion_semantica` se pasa a un segundo embedding model
- **Destino**: `ai.product_image_embeddings` (vector 2048-d + texto descriptivo + índice GIN)

### 5. RAG Context Building (Recuperación)
Se construye el contexto para la decisión LLM:

#### a) Reglas RAG (`ai.rag_rules`)
- Dos tipos de reglas:
  - `artisan_policy`: Define qué es artesanal (hecho a mano, técnicas tradicionales, fibras naturales, etc.)
  - `plagiarism_policy`: Define qué es plagio (misma imagen, distinto productor)

#### b) Búsqueda de Productos Similares
- Se toma el embedding de texto generado
- Se busca en `ai.product_text_embeddings` productos con vectores cercanos (distancia coseno)
- Se excluyen productos del mismo productor
- Se recuperan las últimas decisiones de esos productos similares como ejemplos

#### c) Coherencia Texto-Imagen
- Se compara el embedding textual del producto con el embedding de la descripción visual
- Score: distancia coseno entre ambos vectores
- Resultado: `alta | media | baja | no_evaluada`

#### d) Búsqueda por Descripción Visual Similar
- Se busca en `ai.product_image_embeddings` descripciones semánticas similares
- Se filtran las que tienen decision distinta de null
- Útil para detectar inconsistencias

### 6. Pre-LLM Rules (Reglas sin modelo)
- **Plagio por hash**: Comparación de hashes perceptuales (pHash, dHash) de imágenes
  - Hash exacto → plagio confirmado
  - pHash/dHash con distancia Hamming → posible plagio
- Si se detecta plagio → `revision_humana` directa, sin llamar al LLM

### 7. LLM Decision Model (Generación)
- **Provider**: NVIDIA (primario) → OpenRouter (fallback con `:free`)
- **Modelo**: `nvidia/nemotron-3-nano-omni-30b-a3b-reasoning`
- **Temperatura**: 0.1, max_tokens: 1500
- **System Prompt**: "Eres un validador de productos artesanales... Responde ÚNICAMENTE con JSON"
- **Contexto (User Prompt)**: Contiene todo el evidence construido en el paso 5:
  - Datos del producto (título, descripción, categoría, materiales)
  - Resultado de plagio (hash matching)
  - Coherencia texto-imagen
  - Reglas RAG aplicables
  - Decisiones de productos similares (ejemplos few-shot)
  - Descripción visual generada por el modelo 30B
  - Matches de descripciones visuales similares

### 8. Output del LLM
- **JSON**: `{ decision: "approved|rejected|revision_humana", artesanalidad: { status, score, reason }, motivo_general }`
- Se parsea con regex como fallback si el JSON no es válido

### 9. Persistencia Final
- `ai.product_validation_results`: Resultado completo (UPSERT por `product_id` con `ON CONFLICT DO UPDATE`)
- `tab_productos.validation_status`: Se actualiza el estado del producto

## Diagrama de Flujo (para referencia)

```
[Redis Queue] 
    ↓ brpop
[ValidationWorker]
    ├──→ [Text Embedding] ──→ [product_text_embeddings] (PostgreSQL + pgvector)
    ├──→ [30B Vision Model] ──→ [product_image_embeddings] (descripción + vector 2048-d)
    ├──→ [RAG Context Builder]
    │       ├──→ Reglas RAG (ai.rag_rules)
    │       ├──→ Búsqueda similaridad textual (pgvector cosine)
    │       ├──→ Búsqueda similaridad visual (pgvector cosine)
    │       ├──→ Ejemplos de decisiones previas
    │       └──→ Coherencia texto-imagen
    ├──→ [Pre-LLM Rules] (plagio por hash → revision_humana directa)
    ├──→ [LLM Decision: nemotron-3-nano-omni] (NVIDIA)
    │       └──→ Evidence + RAG context → JSON decision
    └──→ [Save] ──→ [product_validation_results]
                    └──→ [tab_productos.validation_status]
```

## Datos técnicos clave
- **Vector dimension**: 2048
- **Similarity metric**: cosine distance (`<=>` operator)
- **Timeout**: 90s por llamada a provider
- **Queue**: Redis, bloqueante
- **Providers**: OpenRouter (API key), NVIDIA (API key)
- **Db**: PostgreSQL con extensión pgvector
- **ESquema**: `ai` schema en PostgreSQL
- **RAG**: Reglas + few-shot examples + descripción visual multimodal
