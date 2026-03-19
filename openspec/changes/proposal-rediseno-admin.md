# Proposal: Rediseño Premium del Admin Dashboard

## Contexto
El panel de administración en `admin_dashboard.view.php` es funcional pero visualmente básico. Su diseño actual no refleja el estándar "SaaS Premium" (Glassmorphism, jerarquía tipográfica avanzada, micro-interacciones).

## Objetivo
Transformar el UI/UX del dashboard hacia un estándar de alta gama (estilo Stripe/Linear) utilizando TailwindCSS, mejorando la percepción de calidad del lado administrativo de VIVA sin alterar la lógica de negocio subyacente.

## Cambios Propuestos

### 1. Sidebar (Flotante y Glassmorphism)
- Fondo oscuro translúcido con efecto blur extremo (`bg-slate-900/80 backdrop-blur-3xl`).
- Eliminación de bordes duros; uso de resplandor (glow) sutil en el ítem de menú activo mediante `shadow-[inset_4px_0_0_rgb(251,191,36)] bg-white/5`.
- Reducción del peso visual de los textos secundarios.

### 2. Overview Metrics (Neo-Glass Dashboard Hero)
- Las 4 tarjetas de recuento (Usuarios, Artesanos, Ingresos, Pedidos) recibirán un rediseño radical: fondos *dark-glass* (`bg-white/5 border border-white/10`), acompañados de un gradiente radial interno imperceptible.
- Animaciones de interacción orgánicas: `hover:-translate-y-1 hover:shadow-2xl hover:bg-white/10 transition-all duration-300`.
- Mayor grosor de las líneas en gráficos *sparkline*.

### 3. Jerarquía Tipográfica y de Color
- Reemplazo del "texto blanco" absoluto y aburrido por una escala de grises refinada: `text-slate-300` para descripciones largas, `text-white` para cifras numéricas y encabezados clave.
- Para etiquetas estructurales: `text-[10px] tracking-widest font-bold uppercase text-slate-500`.

### 4. Módulo de Parámetros DB
- Los campos del formulario dejarán de ser simples rectángulos blancos.
- Se implementará un diseño "Ghost Input" o "Dark Input" (fondos embebidos `bg-black/20`, borde translúcido `border-white/10`, y foco con brillo mediante `focus:ring-1 focus:ring-amber-500/50`).
- Botones de guardado (`Guardar Cambios`) con sombras en elevación (`shadow-lg shadow-amber-500/20 hover:shadow-amber-500/40`).

### 5. Contenedor Principal (Main)
- Refinamiento de la Topbar para verse más limpia y minimalista, con un recuadro de fecha elegante estilo "pill".
- Fondo de la página principal unificado con las texturas laterales para un "flow" continuo.

## Impacto
El cambio reside enteramente en las clases utilitarias de Tailwind en el archivo `admin_dashboard.view.php`. **No es necesario tocar los scripts JS o los controladores PHP actuales.** Se preservan todos los `id`, atributos `name` de formularios y delegadores `data-action`.
