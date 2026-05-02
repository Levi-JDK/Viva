# TODO - Lighthouse Fixes

## 🔴 Performance (Score: 66%)
- [x] **LCP: 57.8s** — Comprimir `Logo.png` (~4MB → 166KB full) y `artesanias.png` (~7MB → 248KB full). ✅ HECHO
  - `hero.jpeg` se mantuvo como JPEG original (179KB) por calidad apreciable.
  - **Resultado Lighthouse: Performance de 66% → 95%** 🚀
- [x] **Batch procesar imágenes existentes en /images/** — 16 imágenes, de 29.86MB a 3.26MB (89% ahorro). ✅ HECHO
- [x] **Actualizar referencias HTML/PHP** — 13 archivos actualizados, todas las imágenes del frontend ahora apuntan a WebP (excepto hero.jpeg). ✅ HECHO
- [x] **Refactor image_processing.php** — Agregar redimensionamiento + generación de thumbnails (thumb 300x300, medium 600x600, full 1200w). ✅ HECHO
- [x] **FCP: 3.4s** — Preconnect a fonts.googleapis.com y fonts.gstatic.com + display=swap. ✅ HECHO
- [ ] **TTI: 57.8s** — Bundlear los 33 scripts JS en 1-2 archivos (o code-split por ruta).
- [ ] **Unused CSS** — Configurar purge en Tailwind (`content` en `tailwind.config.js`).
- [ ] **Server HTTP/1.1** — Actualizar servidor a HTTP/2 o HTTP/3.
- [x] **Total Byte Weight** — Reducido de ~11MB a ~270KB en login (por imágenes). ✅ HECHO
- [x] **Font Display** — `&display=swap` agregado a Google Fonts + preconnect. ✅ HECHO
- [ ] **Cache Headers** — Configurar cache lifetimes en el servidor web.
- [ ] **Render Blocking Resources** — Defer/async en scripts no críticos.
- [ ] **Image Delivery** — Implementar lazy loading en imágenes below-the-fold + servir variantes thumb/medium según contexto.

## 🔴 Best Practices (Score: 78%)
- [ ] **HTTPS** — Instalar certificado SSL (Let's Encrypt / Certbot) o usar Cloudflare.
- [ ] **Redirect HTTP to HTTPS** — Forzar redirección 301.
- [ ] **BF-Cache** — Revisar eventos que bloquean back/forward cache.

## 🟡 Accessibility (Score: 89%)
- [ ] **Color Contrast** — Revisar ratios de contraste fondo/texto.
- [x] **Landmark** — `<main>` agregado a 6 páginas (index, login, registro, recuperar, registro_vendedor, stand_detail). ✅ HECHO
- [ ] **Link Names** — Agregar `aria-label` o texto visible a links sin nombre.

## 🟢 SEO (Score: 91%)
- [x] **Meta Description** — Agregado en base_head.php con fallback + overrides en login e index. ✅ HECHO

- [ ] **Dinamizar imágenes del frontend en DB** — Crear tabla `page_settings` o `site_assets` para guardar rutas de logo, hero, backgrounds, favicon, etc. Así se pueden cambiar desde admin sin tocar código.

---
**Próximo paso:** Elegir el siguiente ítem para trabajar.
