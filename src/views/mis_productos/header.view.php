
            <!-- Top Header -->
            <header class="h-20 bg-white shadow-sm flex items-center justify-between px-6 z-10">
                <div class="flex items-center">
                    <?php
                        $titles = [
                            'inventory' => 'Mis Productos',
                            'add_product' => 'Subir Producto',
                            'stand' => 'Mi Stand',
                            'statistics' => 'Estadísticas',
                            'configuration' => 'Opciones'
                        ];
                        $current_title = $titles[$_GET['view'] ?? 'inventory'] ?? 'Panel de Control';
                    ?>
                    <button class="lg:hidden text-gray-500 hover:text-gray-700 mr-4" data-action="toggle-sidebar">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 id="pageTitle" class="text-xl font-bold text-gray-800"><?= $current_title ?></h2>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button class="p-2 text-gray-400 hover:text-naranja-artesanal transition-colors relative">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                        </button>
                    </div>
                     <div class="h-8 w-px bg-gray-200 mx-2"></div>
                     <a href="<?= BASE_URL ?>" class="text-sm font-medium text-gray-600 hover:text-naranja-artesanal">
                        Ir al sitio <i class="fas fa-external-link-alt ml-1"></i>
                     </a>
                </div>
            </header>
