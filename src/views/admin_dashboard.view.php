<?php
/**
 * src/views/admin_dashboard.view.php
 * Vista del Panel de Administración — VIVA
 * CSS: compilado en src/styles/output.css (via Tailwind)
 */

$page_title = "Panel Admin — VIVA";
$body_class = "overflow-hidden bg-slate-950 text-slate-300 selection:bg-amber-500/30 font-sans";
$extra_css   = '';

require_once __DIR__ . '/partials/base_head.php';

// ── Definición de menús del sidebar ────────────────────────────────────────
$menus = [
    ['panel_id' => 'overview',   'nom_menu' => 'Resumen General',   'icono_menu' => 'fas fa-home',        'grupo' => 'Resumen',  'color_icon' => 'text-amber-400'],
    ['panel_id' => 'usuarios',   'nom_menu' => 'Usuarios',           'icono_menu' => 'fas fa-users',       'grupo' => 'Gestión',  'color_icon' => 'text-sky-400'],
    ['panel_id' => 'productos',  'nom_menu' => 'Aprobar Productos',  'icono_menu' => 'fas fa-box-open',    'grupo' => 'Gestión',  'color_icon' => 'text-emerald-400'],
    ['panel_id' => 'roles',      'nom_menu' => 'Gestionar Roles',    'icono_menu' => 'fas fa-users-cog',   'grupo' => 'Gestión',  'color_icon' => 'text-rose-400'],
    ['panel_id' => 'crud',       'nom_menu' => 'Gestor de CRUD',     'icono_menu' => 'fas fa-database',    'grupo' => 'Gestión',  'color_icon' => 'text-yellow-400'],
    ['panel_id' => 'reportes',   'nom_menu' => 'Reportes',           'icono_menu' => 'fas fa-chart-line',  'grupo' => 'Gestión',  'color_icon' => 'text-teal-400'],
    ['panel_id' => 'parametros', 'nom_menu' => 'Parámetros DB',      'icono_menu' => 'fas fa-sliders-h',   'grupo' => 'Sistema',  'color_icon' => 'text-slate-400'],
];

$menus_por_grupo = [];
foreach ($menus as $m) {
    $menus_por_grupo[$m['grupo']][] = $m;
}

// ── Configuración de paneles secundarios ────────────────────────────────────
$panel_config = [
    'usuarios'   => ['color' => '#38BDF8', 'icon' => 'fa-users',      'desc' => 'Lista, edita y elimina usuarios. Activa o desactiva accesos de forma segura.'],
    'productos'  => ['color' => '#34D399', 'icon' => 'fa-box-open',   'desc' => 'Gestiona los productos. Revisa calidad, aprueba o solicita modificaciones.'],
    'roles'      => ['color' => '#BC544B', 'icon' => 'fa-users-cog',  'desc' => 'Asigna y revoca menús y permisos a cada rol de usuario.'],
    'crud'       => ['color' => '#D4AF37', 'icon' => 'fa-database',   'desc' => 'Realiza operaciones CRUD directas sobre tablas maestras. '],
    'reportes'   => ['color' => '#2D9E73', 'icon' => 'fa-chart-line', 'desc' => 'Visualiza estadísticas avanzadas y métricas globales del ecosistema.'],
];

// ── Datos de actividad reciente (placeholder) ───────────────────────────────
$actividades = [
    ['color' => '#38BDF8', 'icon' => 'fa-user-plus',           'txt' => 'Nuevo usuario registrado (Invitado)', 'time' => 'Hace 3 min'],
    ['color' => '#D4AF37', 'icon' => 'fa-box-open',            'txt' => 'Producto "Mochila Wayuu" en revisión preliminar',    'time' => 'Hace 12 min'],
    ['color' => '#34D399', 'icon' => 'fa-circle-check',        'txt' => 'Orden de compra enviada exitosamente',  'time' => 'Hace 28 min'],
    ['color' => '#BC544B', 'icon' => 'fa-triangle-exclamation','txt' => 'Producto rechazado (Fotos de baja calidad)',      'time' => 'Hace 1 hora'],
    ['color' => '#a78bfa', 'icon' => 'fa-shield-halved',       'txt' => 'Seguridad: Intento de acceso fallido',  'time' => 'Hace 2 horas'],
];

// ── Accesos rápidos del overview ────────────────────────────────────────────
$accesos = [
    ['panel' => 'usuarios',   'icon' => 'fa-users',      'nom' => 'Usuarios',    'color_icon' => 'text-sky-400',    'border_glow' => 'hover:border-sky-500/30 hover:shadow-[0_0_15px_rgba(56,189,248,0.15)]'],
    ['panel' => 'productos',  'icon' => 'fa-box-open',   'nom' => 'Productos',   'color_icon' => 'text-emerald-400','border_glow' => 'hover:border-emerald-500/30 hover:shadow-[0_0_15px_rgba(52,211,153,0.15)]'],
    ['panel' => 'reportes',   'icon' => 'fa-chart-line', 'nom' => 'Reportes',    'color_icon' => 'text-amber-400',  'border_glow' => 'hover:border-amber-500/30 hover:shadow-[0_0_15px_rgba(251,191,36,0.15)]'],
    ['panel' => 'crud',       'icon' => 'fa-database',   'nom' => 'Base Datos',  'color_icon' => 'text-rose-400',   'border_glow' => 'hover:border-rose-500/30 hover:shadow-[0_0_15px_rgba(244,63,94,0.15)]'],
];
?>

<div class="flex h-screen w-full relative selection:bg-amber-500/30">
    
    <!-- Mobile overlay -->
    <div id="mobile-overlay" onclick="closeSidebar()"
         class="hidden fixed inset-0 z-40 bg-black/60 backdrop-blur-sm transition-opacity md:hidden"></div>

    <!-- ════════════════════════ SIDEBAR ════════════════════════ -->
    <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 w-72 flex flex-col bg-slate-900/80 backdrop-blur-2xl border-r border-white/[0.05] shadow-[4px_0_24px_rgba(0,0,0,0.5)] transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out md:relative md:flex-shrink-0">
        
        <!-- Logo -->
        <div class="h-20 flex items-center px-8 border-b border-white/[0.05]">
            <a href="<?= BASE_URL ?>admin_dashboard" class="flex items-center gap-4 no-underline group hover:-translate-y-0.5 transition-transform">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center shadow-lg shadow-amber-500/20 group-hover:shadow-amber-500/40 transition-all duration-300">
                    <img src="<?= BASE_URL ?>images/Logo.png"
                         alt="VIVA"
                         class="w-full h-full object-cover rounded-xl"
                         onerror="this.style.display='none'; document.getElementById('logo-fallback').style.display='inline'">
                    <span id="logo-fallback" class="text-white font-bold text-xl" style="display:none;">V</span>
                </div>
                <div>
                    <h1 class="text-white font-black tracking-wider text-xl">VIVA</h1>
                    <p class="text-[9px] tracking-[0.2em] font-bold uppercase text-amber-500/80 mt-0.5">Admin Server</p>
                </div>
            </a>
        </div>

        <!-- Navegación -->
        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-8 scrollbar-hide">
            <?php foreach ($menus_por_grupo as $grupo => $items): ?>
                <div>
                    <p class="text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-3 px-4"><?= htmlspecialchars($grupo) ?></p>
                    <div class="space-y-1.5">
                        <?php foreach ($items as $item):
                            $is_active = ($item['panel_id'] === 'overview') ? 'bg-white/5 shadow-[inset_4px_0_0_rgb(251,191,36)] text-white' : 'text-slate-400 hover:bg-white/[0.02] hover:text-slate-200';
                            $icon_active = ($item['panel_id'] === 'overview') ? $item['color_icon'] : 'text-slate-500 group-hover:' . $item['color_icon'];
                        ?>
                        <button
                            data-action="show-panel"
                            data-panel="<?= htmlspecialchars($item['panel_id']) ?>"
                            data-nom="<?= htmlspecialchars($item['nom_menu']) ?>"
                            class="sidebar-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 group <?= $is_active ?>">
                            <i class="<?= htmlspecialchars($item['icono_menu']) ?> w-5 text-center text-lg transition-colors <?= $icon_active ?>"></i>
                            <span class="tracking-wide"><?= htmlspecialchars($item['nom_menu']) ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </nav>

        <!-- Footer de usuario -->
        <div class="h-24 px-6 flex items-center justify-between border-t border-white/[0.05] bg-black/20">
            <div class="flex items-center gap-3 min-w-0">
                <img src="<?= BASE_URL . htmlspecialchars($foto_usuario ?? 'images/profiles/default.webp') ?>"
                     class="w-10 h-10 rounded-full border border-white/10 shadow-lg object-cover"
                     alt="Avatar">
                <div class="min-w-0 pr-2">
                    <p class="text-sm font-bold text-white truncate">
                        <?= htmlspecialchars($nombre_usuario ?? 'Admin Global') ?>
                    </p>
                    <p class="text-[10px] tracking-wide font-medium text-slate-400 truncate">
                        <?= htmlspecialchars($email_usuario ?? 'master@viva.com') ?>
                    </p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>logout" class="w-8 h-8 rounded-full flex items-center justify-center bg-white/5 hover:bg-rose-500/20 hover:text-rose-400 text-slate-400 transition-all duration-200" title="Cerrar Sesión">
                <i class="fas fa-sign-out-alt text-sm"></i>
            </a>
        </div>
    </aside>

    <!-- ════════════════════════ MAIN ════════════════════════ -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-slate-900 via-slate-950 to-black">

        <!-- Topbar -->
        <header class="sticky top-0 z-30 flex items-center justify-between px-8 lg:px-12 h-20 bg-slate-950/50 backdrop-blur-md border-b border-white/[0.02]">
            <div class="flex items-center gap-4">
                <button id="admin-mobile-menu-btn"
                        data-action="toggle-sidebar"
                        class="md:hidden w-10 h-10 rounded-xl flex items-center justify-center bg-white/5 text-white hover:bg-white/10 transition">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="hidden sm:flex items-center gap-2 text-sm font-bold">
                    <span class="text-slate-500">Workspace</span>
                    <span class="text-slate-600">/</span>
                    <span id="panel-title" class="text-white tracking-wide">Resumen General</span>
                </div>
            </div>

            <!-- Cluster derecho -->
            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/5">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)] animate-pulse"></div>
                    <span class="text-[11px] font-bold text-slate-300 uppercase tracking-widest"><?= date('d M Y') ?></span>
                </div>
                <div class="w-px h-6 bg-white/10 hidden md:block"></div>
                <button class="w-10 h-10 rounded-full flex items-center justify-center bg-white/5 text-slate-300 hover:text-white hover:bg-white/10 transition relative">
                    <i class="fas fa-bell"></i>
                    <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-rose-500"></span>
                </button>
                <a href="<?= BASE_URL ?>" target="_blank" class="w-10 h-10 rounded-full flex items-center justify-center bg-amber-500/10 text-amber-500 hover:bg-amber-500 hover:text-slate-900 transition-colors" title="Ver Sitio Frontend">
                    <i class="fas fa-external-link-alt text-sm"></i>
                </a>
            </div>
        </header>

        <!-- Contenido Central -->
        <main class="flex-1 p-8 lg:p-12 relative">

            <!-- ══════ PANEL: OVERVIEW ══════ -->
            <section id="panel-overview" class="admin-panel admin-panel--active block">

                <!-- Welcome -->
                <div class="mb-10 animate-fade-in-up">
                    <h2 class="text-3xl font-black tracking-tight text-white mb-2">
                        Bienvenido, <?= htmlspecialchars(explode(' ', $nombre_usuario ?? 'Admin')[0]) ?> <span class="text-amber-400">👋</span>
                    </h2>
                    <p class="text-sm text-slate-400 font-medium tracking-wide">Aquí tienes una visión global del ecosistema VIVA en tiempo real.</p>
                </div>

                <!-- Métricas (Dashboard Hero Neo-Glass) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">
                    <?php
                    $metrics = [
                        ['label' => 'Usuarios',   'icon' => 'fa-users',       'color' => '#38BDF8', 'bg_glow' => 'from-sky-500/10 to-transparent', 'shadow' => 'shadow-sky-500/5', 'sub' => 'Registrados en total', 'spark' => '0,50 20,38 40,44 60,28 80,32 100,18 120,22'],
                        ['label' => 'Artesanos',  'icon' => 'fa-paint-brush', 'color' => '#f59e0b', 'bg_glow' => 'from-amber-500/10 to-transparent','shadow' => 'shadow-amber-500/5', 'sub' => 'Activos en plataforma', 'spark' => '0,55 20,40 40,48 60,30 80,35 100,20 120,15'],
                        ['label' => 'Ingresos',   'icon' => 'fa-coins',       'color' => '#10B981', 'bg_glow' => 'from-emerald-500/10 to-transparent','shadow' => 'shadow-emerald-500/5', 'sub' => 'Mes actual', 'spark' => '0,45 20,42 40,35 60,38 80,22 100,28 120,12', 'prefix' => '$'],
                        ['label' => 'Pedidos',    'icon' => 'fa-shopping-bag','color' => '#F43F5E', 'bg_glow' => 'from-rose-500/10 to-transparent','shadow' => 'shadow-rose-500/5', 'sub' => 'Total de órdenes', 'spark' => '0,52 20,44 40,46 60,36 80,40 100,24 120,30'],
                    ];
                    foreach ($metrics as $mc): ?>
                        <div class="relative overflow-hidden bg-white/[0.02] border border-white/[0.05] rounded-3xl p-6 hover:-translate-y-1 hover:bg-white/[0.04] transition-all duration-300 group <?= $mc['shadow'] ?>">
                            <!-- Resplandor de fondo radial -->
                            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl <?= $mc['bg_glow'] ?> rounded-full blur-2xl opacity-50 group-hover:opacity-100 transition-opacity"></div>
                            
                            <!-- Sparkline (Fondo) -->
                            <div class="absolute bottom-0 left-0 right-0 h-16 opacity-30 group-hover:opacity-60 transition-opacity">
                                <svg class="w-full h-full" viewBox="0 0 120 60" preserveAspectRatio="none">
                                    <polyline points="<?= $mc['spark'] ?>" stroke="<?= $mc['color'] ?>" stroke-width="2" fill="none" class="drop-shadow-[0_0_8px_rgba(255,255,255,0.2)]"/>
                                    <polyline points="<?= $mc['spark'] ?> 120,60 0,60" fill="<?= $mc['color'] ?>" fill-opacity="0.1"/>
                                </svg>
                            </div>

                            <div class="relative z-10">
                                <div class="flex items-start justify-between mb-8">
                                    <p class="text-[10px] font-bold tracking-widest uppercase text-slate-400 mt-2"><?= $mc['label'] ?></p>
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-black/20 border border-white/5 backdrop-blur-md" style="box-shadow: 0 0 15px <?= $mc['color'] ?>20;">
                                        <i class="fas <?= $mc['icon'] ?> text-lg" style="color:<?= $mc['color'] ?>;"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-4xl font-black text-white tracking-tight mb-1"><?= ($mc['prefix'] ?? '') ?>—</p>
                                    <p class="text-xs text-slate-500 font-medium"><?= $mc['sub'] ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Fila inferior: actividad + accesos rápidos -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Actividad reciente -->
                    <div class="bg-white/[0.02] border border-white/[0.05] rounded-3xl p-8 relative overflow-hidden shadow-2xl">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-sky-500 via-emerald-500 to-amber-500 opacity-30"></div>
                        <div class="mb-8 flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-bold text-white mb-1">Actividad Reciente</h3>
                                <p class="text-[11px] text-slate-400 tracking-wide font-medium uppercase">Últimos eventos del log de auditoría</p>
                            </div>
                            <button class="text-xs text-amber-500 font-bold hover:text-amber-400 transition bg-amber-500/10 px-4 py-2 rounded-full border border-amber-500/20">Ver Todas</button>
                        </div>
                        <div class="space-y-6">
                            <?php foreach ($actividades as $act): ?>
                                <div class="flex items-start gap-4 group">
                                    <div class="relative mt-1">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center bg-black/40 border border-white/10 relative z-10 transition-transform group-hover:scale-110" style="box-shadow: 0 0 10px <?= $act['color'] ?>30;">
                                            <i class="fas <?= $act['icon'] ?> text-xs" style="color:<?= $act['color'] ?>;"></i>
                                        </div>
                                        <div class="absolute top-8 left-1/2 -ml-px w-px h-10 bg-white/5 group-last:hidden"></div>
                                    </div>
                                    <div class="flex-1 pt-1.5 min-w-0">
                                        <p class="text-sm font-bold text-slate-300 group-hover:text-white transition-colors truncate"><?= htmlspecialchars($act['txt']) ?></p>
                                        <p class="text-[11px] font-semibold tracking-wide uppercase text-slate-500 mt-1"><?= $act['time'] ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Accesos rápidos -->
                    <div class="bg-white/[0.02] border border-white/[0.05] rounded-3xl p-8 relative overflow-hidden flex flex-col shadow-2xl">
                        <div class="mb-8">
                            <h3 class="text-base font-bold text-white mb-1">Accesos Directos</h3>
                            <p class="text-[11px] text-slate-400 tracking-wide font-medium uppercase">Ir directo a la gestión operativa</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 flex-1">
                            <?php foreach ($accesos as $ac): ?>
                                <button data-action="show-panel" data-panel-id="<?= $ac['panel'] ?>"
                                    class="relative group rounded-2xl bg-black/20 border border-white/5 p-6 flex flex-col items-center justify-center gap-4 text-center overflow-hidden transition-all duration-300 <?= $ac['border_glow'] ?> hover:-translate-y-1">
                                    <!-- Glow hover interior -->
                                    <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    
                                    <div class="w-12 h-12 rounded-full bg-white/5 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                                        <i class="fas <?= $ac['icon'] ?> text-2xl <?= $ac['color_icon'] ?>"></i>
                                    </div>
                                    <span class="text-sm font-bold text-slate-400 group-hover:text-white transition-colors tracking-wide">
                                        <?= htmlspecialchars($ac['nom']) ?>
                                    </span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            </section>

            <!-- ══════ PANELES SECUNDARIOS EN DESARROLLO ══════ -->
            <?php foreach ($panel_config as $pid => $pcfg):
                if ($pid === 'crud') continue; 
            ?>
            <section id="panel-<?= $pid ?>" class="admin-panel hidden">
                <div class="mb-10">
                    <?php 
                    $nom = '';
                    foreach ($menus as $m) { if ($m['panel_id'] === $pid) { $nom = $m['nom_menu']; break; } }
                    ?>
                    <h2 class="text-3xl font-black tracking-tight text-white mb-2"><?= htmlspecialchars($nom) ?></h2>
                    <p class="text-sm text-slate-400 font-medium tracking-wide">Área modular de sistema.</p>
                </div>
                
                <div class="relative overflow-hidden bg-white/[0.02] border border-white/[0.05] rounded-3xl p-10 flex flex-col items-center text-center max-w-2xl mx-auto mt-20 shadow-2xl">
                    <div class="absolute inset-0 flex items-center justify-center opacity-20 pointer-events-none">
                        <div class="w-64 h-64 rounded-full blur-3xl" style="background:<?= $pcfg['color'] ?>;"></div>
                    </div>
                    <div class="w-24 h-24 rounded-3xl bg-black/40 border border-white/10 shadow-2xl flex items-center justify-center mb-8 relative z-10" style="box-shadow: 0 10px 40px -10px <?= $pcfg['color'] ?>60;">
                        <i class="fas <?= $pcfg['icon'] ?> text-4xl" style="color:<?= $pcfg['color'] ?>;"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4 relative z-10"><?= htmlspecialchars($nom) ?></h3>
                    <p class="text-slate-400 leading-relaxed font-medium mb-8 relative z-10"><?= $pcfg['desc'] ?></p>
                    <div class="flex items-center gap-3 px-6 py-3 rounded-full bg-black/40 border relative z-10 backdrop-blur-md shadow-inner" style="border-color: <?= $pcfg['color'] ?>30;">
                        <span class="relative flex h-2.5 w-2.5">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background:<?= $pcfg['color'] ?>;"></span>
                          <span class="relative inline-flex rounded-full h-2.5 w-2.5" style="background:<?= $pcfg['color'] ?>;"></span>
                        </span>
                        <span class="text-xs font-bold tracking-widest uppercase text-white">Módulo en Desarrollo Activo</span>
                    </div>
                </div>
            </section>
            <?php endforeach; ?>

            <!-- ══════ PANEL: CRUD DINÁMICO ══════ -->
            <section id="panel-crud" class="admin-panel hidden relative">
                <div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-end gap-6">
                    <div>
                        <h2 class="text-3xl font-black tracking-tight text-white mb-2">Gestor de Diccionarios y Maestras</h2>
                        <p class="text-sm text-slate-400 font-medium tracking-wide">CRUD asíncrono universal sobre 17 tablas del sistema.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="relative group">
                            <i class="fas fa-database absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-amber-400 transition-colors"></i>
                            <select id="crud-entity-selector" class="bg-black/40 border border-white/10 rounded-xl pl-12 pr-10 py-3 text-sm font-bold text-white focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/50 focus:outline-none transition-all cursor-pointer shadow-inner">
                                <option value="" disabled selected>Seleccione Entidad</option>
                                <option value="banco">Bancos</option>
                                <option value="categoria">Categorías</option>
                                <option value="ciudad">Ciudades</option>
                                <option value="color">Colores</option>
                                <option value="departamento">Departamentos</option>
                                <option value="forma_pago">Formas de Pago</option>
                                <option value="grupo">Grupos</option>
                                <option value="idioma">Idiomas</option>
                                <option value="materia">Materias Primas</option>
                                <option value="moneda">Monedas</option>
                                <option value="oficio">Oficios</option>
                                <option value="pais">Países</option>
                                <option value="parametros">Parámetros DB</option>
                                <option value="tipo_doc">Tipos de Documento</option>
                                <option value="transito">Tránsito Aduana</option>
                                <option value="transportadora">Transportadoras</option>
                            </select>
                        </div>
                        <button id="crud-btn-new" disabled class="disabled:opacity-50 disabled:cursor-not-allowed group relative px-6 py-3 bg-amber-500 hover:bg-amber-400 text-slate-900 text-sm font-bold rounded-xl transition-all duration-300 hover:-translate-y-1 shadow-[0_5px_20px_-5px_rgba(245,158,11,0.5)]">
                            <i class="fas fa-plus mr-2"></i> Nuevo
                        </button>
                    </div>
                </div>

                <!-- Contenedor Tabla Glassmorphism -->
                <div class="bg-slate-900 border border-white/10 rounded-3xl p-6 relative shadow-[0_0_40px_rgba(0,0,0,0.8)] min-h-[400px]">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-amber-500/5 rounded-full blur-3xl pointer-events-none -mt-32 -mr-32"></div>
                    
                    <div id="crud-loader" class="absolute inset-0 z-10 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center hidden">
                        <i class="fas fa-circle-notch fa-spin text-4xl text-amber-400"></i>
                    </div>

                    <div id="crud-empty-state" class="absolute inset-0 flex flex-col items-center justify-center text-center p-8 z-0">
                        <div class="w-16 h-16 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-slate-600 mb-4 shadow-inner">
                            <i class="fas fa-mouse-pointer text-2xl"></i>
                        </div>
                        <p class="text-slate-400 font-bold tracking-wide">Selecciona una entidad en el menú superior para comenzar.</p>
                    </div>

                    <div class="overflow-x-auto relative z-10 h-full scrollbar-thin">
                        <table class="w-full text-left border-collapse hidden" id="crud-table">
                            <thead>
                                <tr class="border-b border-white/10 text-[10px] font-bold tracking-widest uppercase text-slate-500" id="crud-thead-tr">
                                    <!-- Dinámico -->
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-sm text-slate-300 font-medium" id="crud-tbody">
                                <!-- Dinámico -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- ══════ MODAL CRUD (Ghost UI) ══════ -->
            <div id="crud-modal" class="fixed inset-0 z-[100] bg-black/60 backdrop-blur-sm hidden items-center justify-center opacity-0 transition-opacity duration-300">
                <div class="bg-slate-900 border border-white/10 rounded-3xl shadow-[0_0_50px_rgba(0,0,0,0.8)] w-full max-w-lg overflow-hidden transform scale-95 transition-transform duration-300 relative">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-amber-400 to-amber-600"></div>
                    
                    <div class="px-8 py-6 border-b border-white/5 flex items-center justify-between bg-white/[0.02]">
                        <div>
                            <h3 class="text-xl font-black text-white" id="crud-modal-title">Nuevo Registro</h3>
                            <p class="text-[11px] text-slate-400 uppercase tracking-widest font-bold mt-1" id="crud-modal-subtitle">Entidad</p>
                        </div>
                        <button id="crud-modal-close" class="w-8 h-8 rounded-full bg-white/5 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 flex items-center justify-center transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="p-8 pb-10">
                        <form id="crud-form" class="space-y-5">
                            <div id="crud-form-fields" class="space-y-5">
                                <!-- Dinámico Ghost Inputs -->
                            </div>
                            <div class="mt-8 flex justify-end gap-4 pt-4 border-t border-white/5">
                                <button type="button" id="crud-btn-cancel" class="px-5 py-2.5 rounded-xl font-bold text-sm text-slate-400 hover:text-white hover:bg-white/5 transition-colors">Cancelar</button>
                                <button type="submit" id="crud-btn-save" class="px-6 py-2.5 rounded-xl font-bold text-sm bg-amber-500 hover:bg-amber-400 text-slate-900 shadow-[0_0_15px_rgba(245,158,11,0.3)] transition-all hover:scale-105 flex items-center gap-2">
                                    <i class="fas fa-save hidden" id="crud-save-icon"></i> 
                                    <span>Guardar Datos</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ══════ PANEL: PARÁMETROS DB Y LANDING ══════ -->
            <section id="panel-parametros" class="admin-panel hidden">
                
                <div class="mb-10 flex flex-col md:flex-row md:justify-between md:items-end gap-6">
                    <div>
                        <h2 class="text-3xl font-black tracking-tight text-white mb-2">Parámetros DB y Sistema</h2>
                        <p class="text-sm text-slate-400 font-medium tracking-wide">Configuración ultra-segura de las variables maestras de VIVA.</p>
                    </div>
                    <button type="submit" form="form-parametros" class="group relative px-6 py-3.5 bg-amber-500 hover:bg-amber-400 text-slate-900 text-sm font-bold rounded-xl transition-all duration-300 hover:-translate-y-1" style="box-shadow: 0 10px 30px -10px rgba(245, 158, 11, 0.8);">
                        <i class="fas fa-save mr-2 group-hover:scale-110 transition-transform"></i> Guardar Configuración
                    </button>
                </div>

                <form id="form-parametros" class="space-y-8 max-w-6xl pb-20">
                    
                    <!-- Global Settings -->
                    <div class="bg-white/[0.02] border border-white/[0.05] rounded-3xl p-8 lg:p-10 relative shadow-2xl overflow-hidden">
                        <div class="absolute top-0 right-0 w-96 h-96 bg-sky-500/5 rounded-full blur-3xl pointer-events-none -mt-20 -mr-20"></div>
                        <div class="mb-10 flex items-center gap-4 relative z-10">
                            <div class="w-12 h-12 rounded-xl bg-black/40 border border-white/5 flex items-center justify-center text-sky-400 shadow-inner">
                                <i class="fas fa-globe text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white mb-1">Entorno Global de la Empresa</h3>
                                <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest">Identificadores y contacto matriz</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 relative z-10">
                            <!-- Ghost Inputs pro -->
                            <div class="group">
                                <label class="block text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-2.5 group-focus-within:text-sky-400 transition-colors">Nombre Plataforma Central</label>
                                <input type="text" name="nom_plataforma" value="<?= htmlspecialchars($pmtros['nom_plataforma'] ?? '') ?>" 
                                       class="w-full bg-black/20 border border-white/5 rounded-xl px-5 py-3.5 text-sm font-bold text-white placeholder-slate-600 focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/50 focus:outline-none transition-all shadow-inner">
                            </div>
                            <div class="group">
                                <label class="block text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-2.5 group-focus-within:text-sky-400 transition-colors">Correo Electrónico Matriz</label>
                                <input type="email" name="correo_contacto" value="<?= htmlspecialchars($pmtros['correo_contacto'] ?? '') ?>" 
                                       class="w-full bg-black/20 border border-white/5 rounded-xl px-5 py-3.5 text-sm font-bold text-white focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/50 focus:outline-none transition-all shadow-inner">
                            </div>
                            <div class="group col-span-full lg:col-span-1">
                                <label class="block text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-2.5 group-focus-within:text-sky-400 transition-colors">Sede Corporativa Fsica</label>
                                <input type="text" name="dir_contacto" value="<?= htmlspecialchars($pmtros['dir_contacto'] ?? '') ?>" 
                                       class="w-full bg-black/20 border border-white/5 rounded-xl px-5 py-3.5 text-sm font-bold text-white focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/50 focus:outline-none transition-all shadow-inner">
                            </div>
                            
                            <div class="col-span-full h-px bg-white/5 my-2"></div>
                            
                            <div class="group">
                                <label class="block text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-2.5 group-focus-within:text-rose-500 transition-colors">Folio Inicial de Facturación</label>
                                <input type="number" name="val_inifact" value="<?= htmlspecialchars($pmtros['val_inifact'] ?? '') ?>" 
                                       class="w-full bg-black/20 border border-rose-500/20 rounded-xl px-5 py-3.5 text-sm font-mono font-bold text-rose-100 focus:border-rose-500/80 focus:ring-1 focus:ring-rose-500/50 focus:outline-none transition-all shadow-inner">
                            </div>
                            <div class="group">
                                <label class="block text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-2.5 group-focus-within:text-emerald-500 transition-colors">Consecutivo Actual Emitido</label>
                                <input type="number" name="val_actfact" value="<?= htmlspecialchars($pmtros['val_actfact'] ?? '') ?>" 
                                       class="w-full bg-black/20 border border-emerald-500/20 rounded-xl px-5 py-3.5 text-sm font-mono font-bold text-emerald-100 focus:border-emerald-500/80 focus:ring-1 focus:ring-emerald-500/50 focus:outline-none transition-all shadow-inner">
                            </div>
                        </div>
                    </div>

                    <!-- Landing Settings - Filosofía y Confianza -->
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                        
                        <div class="bg-white/[0.02] border border-white/[0.05] rounded-3xl p-8 lg:p-10 shadow-2xl flex flex-col relative overflow-hidden">
                            <div class="absolute bottom-0 left-0 w-full h-96 bg-emerald-500/5 blur-3xl pointer-events-none rounded-full translate-y-1/2"></div>
                            <div class="mb-10 flex items-center gap-4 relative z-10">
                                <div class="w-12 h-12 rounded-xl bg-black/40 border border-white/5 flex items-center justify-center text-emerald-400 shadow-inner">
                                    <i class="fas fa-leaf text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-white mb-1">Declaración de Marca frontend</h3>
                                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest">Párrafos de filosofía para clientes</p>
                                </div>
                            </div>
                            <div class="space-y-8 flex-1 flex flex-col relative z-10">
                                <div class="group">
                                    <label class="block text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-2.5 group-focus-within:text-emerald-400 transition-colors">Título de la sección sobre nosotros</label>
                                    <input type="text" name="landing_filosofia_tit" value="<?= htmlspecialchars($pmtros['landing_filosofia_tit'] ?? '') ?>" 
                                           class="w-full bg-black/20 border border-white/5 rounded-xl px-5 py-3.5 text-sm font-bold text-white focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 focus:outline-none transition-all shadow-inner">
                                </div>
                                <div class="group">
                                    <label class="block text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-2.5 group-focus-within:text-emerald-400 transition-colors">Manifiesto Empresarial - Párrafo 1</label>
                                    <textarea name="landing_filosofia_p1" class="w-full bg-black/20 border border-white/5 rounded-xl px-5 py-5 text-sm font-bold text-slate-300 leading-relaxed focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 focus:outline-none transition-all shadow-inner resize-none min-h-[100px]"><?= htmlspecialchars($pmtros['landing_filosofia_p1'] ?? '') ?></textarea>
                                </div>
                                <div class="group flex-1 flex flex-col">
                                    <label class="block text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-2.5 group-focus-within:text-emerald-400 transition-colors">Manifiesto de Impacto - Párrafo 2</label>
                                    <textarea name="landing_filosofia_p2" class="flex-1 w-full bg-black/20 border border-white/5 rounded-xl px-5 py-5 text-sm font-bold text-slate-300 leading-relaxed focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 focus:outline-none transition-all shadow-inner resize-none min-h-[100px]"><?= htmlspecialchars($pmtros['landing_filosofia_p2'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Panel Confianza -->
                        <div class="bg-white/[0.02] border border-white/[0.05] rounded-3xl p-8 lg:p-10 shadow-2xl relative overflow-hidden">
                            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-amber-500/5 blur-3xl pointer-events-none rounded-full"></div>
                            
                            <div class="mb-10 flex items-center gap-4 relative z-10">
                                <div class="w-12 h-12 rounded-xl bg-black/40 border border-white/5 flex items-center justify-center text-amber-500 shadow-inner">
                                    <i class="fas fa-shield-alt text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-white mb-1">Garantías de Plataforma</h3>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Iconos y copys de seguridad mostrados</p>
                                </div>
                            </div>
                            
                            <div class="space-y-5 relative z-10">
                                <!-- Conf 1 -->
                                <div class="bg-black/40 rounded-2xl p-6 border border-white/5 flex flex-col sm:flex-row gap-5 focus-within:border-amber-500/40 transition-colors relative overflow-hidden group shadow-inner">
                                    <div class="absolute inset-y-0 left-0 w-1.5 bg-gradient-to-b from-amber-400 to-amber-600 opacity-0 group-focus-within:opacity-100 transition-opacity"></div>
                                    <div class="w-14 h-14 rounded-xl bg-white/5 flex shrink-0 items-center justify-center text-amber-500/40 group-focus-within:text-amber-400 transition-colors border border-white/5"><i class="fas fa-shipping-fast text-2xl"></i></div>
                                    <div class="flex-1 space-y-3">
                                        <div class="relative">
                                            <span class="absolute right-0 top-3 text-[9px] font-bold text-white/20 uppercase tracking-widest pointer-events-none">H3</span>
                                            <input type="text" name="landing_conf_1_tit" value="<?= htmlspecialchars($pmtros['landing_conf_1_tit'] ?? '') ?>" 
                                                class="w-full bg-transparent border-b border-white/10 px-1 py-1 text-sm font-bold text-white focus:border-amber-400 focus:outline-none transition-colors" placeholder="Garantía 1">
                                        </div>
                                        <div class="relative">
                                            <span class="absolute right-0 top-3 text-[9px] font-bold text-white/20 uppercase tracking-widest pointer-events-none">Sub</span>
                                            <input type="text" name="landing_conf_1_sub" value="<?= htmlspecialchars($pmtros['landing_conf_1_sub'] ?? '') ?>" 
                                                class="w-full bg-transparent border-b border-transparent px-1 py-1 text-xs font-bold text-slate-400 focus:border-white/20 focus:text-slate-300 focus:outline-none transition-colors" placeholder="Explicación">
                                        </div>
                                    </div>
                                </div>

                                <!-- Conf 2 -->
                                <div class="bg-black/40 rounded-2xl p-6 border border-white/5 flex flex-col sm:flex-row gap-5 focus-within:border-amber-500/40 transition-colors relative overflow-hidden group shadow-inner">
                                    <div class="absolute inset-y-0 left-0 w-1.5 bg-gradient-to-b from-amber-400 to-amber-600 opacity-0 group-focus-within:opacity-100 transition-opacity"></div>
                                    <div class="w-14 h-14 rounded-xl bg-white/5 flex shrink-0 items-center justify-center text-amber-500/40 group-focus-within:text-amber-400 transition-colors border border-white/5"><i class="fas fa-lock text-2xl"></i></div>
                                    <div class="flex-1 space-y-3">
                                        <div class="relative">
                                            <span class="absolute right-0 top-3 text-[9px] font-bold text-white/20 uppercase tracking-widest pointer-events-none">H3</span>
                                            <input type="text" name="landing_conf_2_tit" value="<?= htmlspecialchars($pmtros['landing_conf_2_tit'] ?? '') ?>" 
                                                class="w-full bg-transparent border-b border-white/10 px-1 py-1 text-sm font-bold text-white focus:border-amber-400 focus:outline-none transition-colors" placeholder="Garantía 2">
                                        </div>
                                        <div class="relative">
                                            <span class="absolute right-0 top-3 text-[9px] font-bold text-white/20 uppercase tracking-widest pointer-events-none">Sub</span>
                                            <input type="text" name="landing_conf_2_sub" value="<?= htmlspecialchars($pmtros['landing_conf_2_sub'] ?? '') ?>" 
                                                class="w-full bg-transparent border-b border-transparent px-1 py-1 text-xs font-bold text-slate-400 focus:border-white/20 focus:text-slate-300 focus:outline-none transition-colors" placeholder="Explicación">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Conf 3 -->
                                <div class="bg-black/40 rounded-2xl p-6 border border-white/5 flex flex-col sm:flex-row gap-5 focus-within:border-amber-500/40 transition-colors relative overflow-hidden group shadow-inner">
                                    <div class="absolute inset-y-0 left-0 w-1.5 bg-gradient-to-b from-amber-400 to-amber-600 opacity-0 group-focus-within:opacity-100 transition-opacity"></div>
                                    <div class="w-14 h-14 rounded-xl bg-white/5 flex shrink-0 items-center justify-center text-amber-500/40 group-focus-within:text-amber-400 transition-colors border border-white/5"><i class="fas fa-hand-holding-heart text-2xl"></i></div>
                                    <div class="flex-1 space-y-3">
                                        <div class="relative">
                                            <span class="absolute right-0 top-3 text-[9px] font-bold text-white/20 uppercase tracking-widest pointer-events-none">H3</span>
                                            <input type="text" name="landing_conf_3_tit" value="<?= htmlspecialchars($pmtros['landing_conf_3_tit'] ?? '') ?>" 
                                                class="w-full bg-transparent border-b border-white/10 px-1 py-1 text-sm font-bold text-white focus:border-amber-400 focus:outline-none transition-colors" placeholder="Garantía 3">
                                        </div>
                                        <div class="relative">
                                            <span class="absolute right-0 top-3 text-[9px] font-bold text-white/20 uppercase tracking-widest pointer-events-none">Sub</span>
                                            <input type="text" name="landing_conf_3_sub" value="<?= htmlspecialchars($pmtros['landing_conf_3_sub'] ?? '') ?>" 
                                                class="w-full bg-transparent border-b border-transparent px-1 py-1 text-xs font-bold text-slate-400 focus:border-white/20 focus:text-slate-300 focus:outline-none transition-colors" placeholder="Explicación">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </section>

        </main>
    </div>

<style>
/* CSS adicional estricto mínimo, el resto es Tailwind utility */
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.fade-in { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
.delay-100 { animation-delay: 100ms; }
.delay-200 { animation-delay: 200ms; }
</style>

<!-- Scripts -->
<!-- Scripts limpios movidos a Clean Architecture -->
<script src="<?= BASE_URL ?>src/scripts/admin_crud.js"></script>
</body>
</html>