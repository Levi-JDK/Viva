# Aprendizajes - Optimización Lighthouse (VIVA)

> Fecha: 2026-04-30
> Auditoría: Lighthouse v13.0.2
> URL auditada: http://135.119.114.214/viva/login

---

## Scores Iniciales

| Categoría | Score | Estado |
|-----------|-------|--------|
| Performance | 66% | Regular — afectado por imágenes gigantes |
| Accessibility | 89% | Bastante bien |
| Best Practices | 78% | Medio pelo |
| SEO | 91% | Bien |

---

## Problemas Críticos

### 1. LCP: 57.8 segundos (score: 0)
- **Causa:** `Logo.png` (~4 MB) y `artesanias.png` (~7 MB) son PNG sin comprimir.
- **Fix:** Convertir a WebP con calidad 80-85 y redimensionar al tamaño real de visualización.

### 2. FCP: 3.4 segundos (score: 0.37)
- **Causa:** 33 requests de scripts JS individuales bloquean el render.
- **Fix:** Bundlear JS en 1-2 archivos. Defer/async en scripts no críticos.

### 3. Time to Interactive: 57.8s (score: 0)
- **Causa:** Las imágenes gigantes tapan el thread de red.
- **Fix:** Comprimir imágenes + bundlear JS + HTTP/2.

### 4. No HTTPS (score: 0)
- **Causa:** 39 requests inseguros por HTTP.
- **Fix:** Instalar certificado SSL (Let's Encrypt / Certbot) + redirección 301 HTTP→HTTPS.

### 5. Unused CSS (score: 0)
- **Causa:** `output.css` (105 KB) tiene código muerto de Tailwind.
- **Fix:** Configurar `content` en `tailwind.config.js` para purgar CSS no usado.

### 6. Cache Headers (score: 0)
- **Causa:** El servidor no envía headers de cache.
- **Fix:** Configurar cache lifetimes en Apache/Nginx.

---

## Qué es un Thumbnail

Un **thumbnail** es una versión pequeña y comprimida de una imagen original.

### Por qué son necesarios:
- La imagen original puede ser de 2000x2000px o más.
- En una lista de productos solo se muestra a 300x300px.
- Servir la imagen original es un desperdicio de ancho de banda y ralentiza la carga.

### Ejemplo práctico:
| Contexto | Tamaño recomendado |
|----------|-------------------|
| Listado de productos | 300x300px |
| Detalle de producto | 600x600px |
| Banner / Hero | 1200x400px |
| Imagen original (zoom) | Resolución real |

### Implementación:
- Al subir una imagen, generar automáticamente las variantes (thumbnail, medium, full).
- Servir la versión adecuada según el contexto.
- Esto reduce drásticamente el peso de la página y mejora el LCP.

---

## Sobre WebP y Compresión

- **WebP comprime mucho más que PNG/JPEG.**
  - PNG → JPEG: ~60-70% del tamaño original.
  - JPEG → WebP (calidad 80): ~25-35% del JPEG.
- **Calidad 80 es prácticamente imperceptible** a simple vista. Es el estándar de la industria.
- **Si la imagen tiene transparencia**, WebP la soporta (a diferencia de JPEG).

### Script actual (`image_processing.php`):
- Convierte a WebP con calidad 80.
- Elimina el archivo original.
- **PERO no redimensiona ni genera thumbnails.** Eso hay que agregarlo.

---

## Acciones Prioritarias (TODO)

1. Comprimir `Logo.png` y `artesanias.png` a WebP + redimensionar.
2. Implementar HTTPS + redirección 301.
3. Bundlear los 33 scripts JS en 1-2 archivos.
4. Agregar redimensionamiento + thumbnails al `image_processing.php`.
5. Configurar purge de CSS en Tailwind.
6. Agregar headers de cache en el servidor.
7. Arreglar accesibilidad: contraste, `<main>` landmark, `aria-label` en links.
8. Agregar meta description.

---

## Archivos Relacionados

- `/var/www/html/viva/src/utils/image_processing.php` — Conversión a WebP.
- `/var/www/html/viva/src/utils/image_uploader.php` — Upload handler.
- `/var/www/html/viva/docs/TODO.md` — Checklist de tareas.
- `/var/www/html/viva/docs/lighthouse-learning.md` — Este archivo.

---

## Próximos Fixes Rápidos (Explicación)

### 1. Font Display Swap

**Qué es:** `font-display: swap` es una propiedad CSS que le dice al navegador: "mostrá el texto inmediatamente con una fuente del sistema, y cuando la fuente personalizada (Google Fonts) termine de cargar, hacé el swap (cambio)".

**Por qué falla ahora:** Google Fonts se carga con `@font-face` sin `font-display`. El navegador espera a que la fuente descargue antes de mostrar **cualquier texto**. Eso se llama **Flash of Invisible Text (FOIT)** — el usuario ve una página en blanco durante 1-3 segundos.

**Cómo se arregla:** Agregar `&display=swap` al final de la URL de Google Fonts:
```html
<!-- Antes (sin swap) -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<!-- Después (con swap) -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
```
Google Fonts ya lo soporta nativamente con ese parámetro.

**Impacto esperado:** Reduce FCP en ~0.5-1.5 segundos. Lighthouse suele dar +2-5 puntos de Performance.

---

### 2. Meta Description

**Qué es:** Es una etiqueta `<meta>` en el `<head>` del HTML que describe el contenido de la página en 1-2 oraciones. Google la usa para mostrar el snippet (resumen) en los resultados de búsqueda.

**Por qué falla ahora:** No existe `<meta name="description">` en ninguna vista. Lighthouse lo detecta como ausente.

**Cómo se arregla:** Agregar en `base_head.php` (compartido por todas las páginas):
```html
<meta name="description" content="VIVA — Artesanías Colombianas. Conectamos tradiciones milenarias con el mundo moderno. Compra artesanías únicas hechas a mano por comunidades indígenas de Colombia.">
```

**Impacto esperado:** Lighthouse SEO sube de 91% a ~95-98%. No afecta Performance.

---

### 3. Main Landmark (`<main>`)

**Qué es:** Un **landmark** es una región semántica del documento HTML que los lectores de pantalla (para personas ciegas) usan para navegar rápidamente. `<main>` define el contenido principal de la página.

**Por qué falla ahora:** El contenido de cada página está envuelto en `<div>` genéricos sin `<main>` ni `role="main"`. Los lectores de pantalla no pueden saltar directamente al contenido.

**Cómo se arregla:** Envolver el contenido principal de cada layout con `<main>`:
```html
<!-- Antes -->
<body>
    <header>...</header>
    <div class="container">...</div>
    <footer>...</footer>
</body>

<!-- Después -->
<body>
    <header>...</header>
    <main>
        <div class="container">...</div>
    </main>
    <footer>...</footer>
</body>
```

**Impacto esperado:** Lighthouse Accessibility sube de 89% a ~93-95%. Es un cambio puramente semántico — no afecta el diseño visual si se usan CSS resets correctos.

---

**Orden recomendado de implementación:**
1. Font Display Swap (1 minuto, impacto en Performance)
2. Meta Description (2 minutos, impacto en SEO)
3. Main Landmark (5 minutos, impacto en Accessibility)

---

## Fixes Implementados (2026-05-01)

### 1. Font Display Swap + Preconnect ✅

**Qué se hizo:**
- Se agregaron 2 `<link rel="preconnect">` a `fonts.googleapis.com` y `fonts.gstatic.com` en `base_head.php`.
- Se cambió la carga de Google Fonts de `@import` en `input.css` a `<link>` en `base_head.php` con `&display=swap`.
- Se eliminó el `@import` duplicado de `input.css` y `output.css`.

**Por qué funciona:**
- `preconnect` hace que el navegador establezca la conexión TCP+TLS con los servidores de fonts **antes** de que el CSS las necesite, ahorrando 2-3 round-trips.
- `display=swap` elimina el FOIT (Flash of Invisible Text). El texto se muestra inmediatamente con Arial/Helvetica y hace el swap cuando Outfit descarga.
- Pasar de `@import` a `<link>` permite que el navegador descubra la fuente **en paralelo** con el CSS, no después.

**Resultado esperado:** FCP mejora ~0.5-1.5s. Performance +2-5 puntos.

---

### 2. Meta Description ✅

**Qué se hizo:**
- Se agregó `<meta name="description">` en `base_head.php` con fallback genérico usando `htmlspecialchars($page_description ?? 'default')`.
- Se agregaron descripciones personalizadas (`$page_description`) en:
  - `login.view.php` — "Inicia sesión en VIVA para acceder a artesanías colombianas únicas."
  - `index.view.php` — "Descubre artesanías colombianas hechas a mano por comunidades indígenas."
  - `stand_detail.view.php` — descripción dinámica del stand.

**Por qué funciona:**
- Cada página ahora tiene una descripción única para Google.
- El fallback garantiza que ninguna página quede sin description.
- `htmlspecialchars` previene XSS si el contenido viene de la DB.

**Resultado esperado:** SEO sube de 91% a ~95-97%.

---

### 3. Main Landmark (`<main>`) ✅

**Qué se hizo:**
- Se agregó **exactamente un `<main>`** por página en 6 vistas:
  - `index.view.php`
  - `login.view.php`
  - `registro.view.php`
  - `recuperar.view.php`
  - `registro_vendedor.view.php`
  - `stand_detail.view.php`
- El `<main>` envuelve **solo el contenido principal**, excluyendo header, navbar, footer y sidebars.

**Por qué funciona:**
- Los lectores de pantalla (NVDA, JAWS, VoiceOver) pueden saltar directamente al contenido principal con un atajo de teclado.
- Mejora la navegación para personas con discapacidad visual.
- Es una regla WCAG 2.1 AA.

**Regla clave:** Nunca más de un `<main>` por página. Si hay sidebars, van en `<aside>`, no dentro de `<main>`.

**Resultado esperado:** Accessibility sube de 89% a ~93-95%.
