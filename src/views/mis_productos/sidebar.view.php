
        <!-- Sidebar Navigation -->
        <aside id="sidebar" class="w-64 flex-shrink-0 bg-gradient-to-b from-tierra-oscuro to-secundario text-white flex flex-col transition-all duration-300 transform lg:translate-x-0 -translate-x-full fixed lg:static z-30 h-full shadow-2xl">
            <!-- Logo Section -->
            <div class="h-20 flex items-center justify-start border-b border-white/10 pl-8">
                <a href="<?= BASE_URL ?>" class="flex items-center space-x-2 group">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center bg-opacity-20 backdrop-blur-sm group-hover:bg-opacity-30 transition-all">
                        <img src="<?= BASE_URL ?>images/Logo_thumb.webp" alt="VIVA" class="w-8 h-8 object-contain rounded-lg">
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-wider">VIVA</h1>
                        <p class="text-[10px] uppercase tracking-widest opacity-80">Vendedor</p>
                    </div>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto py-6 space-y-1">
                <?php $current_view = $_GET['view'] ?? 'inventory'; ?>
                
                <a href="?view=inventory" class="menu-item w-full flex items-center space-x-4 px-6 py-3.5 text-left <?= $current_view === 'inventory' ? 'active-item' : '' ?>">
                    <i class="fas fa-box w-5 text-center"></i>
                    <span class="font-semibold">Mis Productos</span>
                </a>
                <a href="?view=add_product" class="menu-item w-full flex items-center space-x-4 px-6 py-3.5 text-left <?= $current_view === 'add_product' ? 'active-item' : '' ?>">
                    <i class="fas fa-plus-circle w-5 text-center"></i>
                    <span class="font-semibold">Subir Producto</span>
                </a>
                <a href="?view=stand" class="menu-item w-full flex items-center space-x-4 px-6 py-3.5 text-left <?= $current_view === 'stand' ? 'active-item' : '' ?>">
                    <i class="fas fa-store w-5 text-center"></i>
                    <span class="font-semibold">Mi Stand</span>
                </a>
                <a href="?view=statistics" class="menu-item w-full flex items-center space-x-4 px-6 py-3.5 text-left <?= $current_view === 'statistics' ? 'active-item' : '' ?>">
                    <i class="fas fa-chart-line w-5 text-center"></i>
                    <span class="font-semibold">Estadísticas</span>
                </a>
                <div class="pt-4 pb-2 px-6">
                    <p class="text-xs uppercase text-white/50 font-bold tracking-wider">Configuración</p>
                </div>
                <a href="?view=configuration" class="menu-item w-full flex items-center space-x-4 px-6 py-3.5 text-left <?= $current_view === 'configuration' ? 'active-item' : '' ?>">
                    <i class="fas fa-cog w-5 text-center"></i>
                    <span class="font-semibold">Opciones</span>
                </a>
                <a href="<?= BASE_URL ?>perfil" class="menu-item w-full flex items-center space-x-4 px-6 py-3.5 text-left text-white/80 hover:text-white">
                    <i class="fas fa-user-circle w-5 text-center"></i>
                    <span class="font-semibold">Mi Perfil</span>
                </a>
            </nav>

            <!-- User Footer -->
            <div class="p-4 border-t border-white/10 bg-black/10">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center overflow-hidden">
                         <img src="<?= BASE_URL . $foto_usuario ?>?v=<?= time() ?>" alt="User" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate"><?= htmlspecialchars($nombre_usuario) ?></p>
                        <p class="text-xs text-white/60 truncate">Vendedor</p>
                    </div>
                    <a href="<?= BASE_URL ?>logout" class="text-white/60 hover:text-white p-2" title="Cerrar Sesión">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Overlay for mobile sidebar -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden" data-action="toggle-sidebar"></div>
