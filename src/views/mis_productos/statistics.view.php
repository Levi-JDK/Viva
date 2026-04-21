
                    <h2 class="text-2xl font-bold text-white mb-6">Resumen de Estadísticas</h2>
                    
                     <!-- KPI Cards -->
                     <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                         <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-naranja-artesanal flex justify-between items-start">
                              <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Productos Totales</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?= number_format($stats['total_productos'] ?? 0) ?></h3>
                                <p class="text-xs text-gray-400 mt-1">Inventario registrado</p>
                              </div>
                              <div class="p-2 bg-orange-50 rounded-lg text-naranja-artesanal">
                                  <i class="fas fa-wallet text-xl"></i>
                              </div>
                         </div>
                         <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500 flex justify-between items-start">
                              <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Visitas Productos</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?= number_format($stats['vistas_totales'] ?? 0) ?></h3>
                                <p class="text-xs text-gray-400 mt-1">Acumulado actual</p>
                              </div>
                              <div class="p-2 bg-blue-50 rounded-lg text-blue-500">
                                  <i class="fas fa-users text-xl"></i>
                              </div>
                         </div>
                         <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-purple-500 flex justify-between items-start">
                              <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Stock Total</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?= number_format($stats['stock_total'] ?? 0) ?></h3>
                                <p class="text-xs text-gray-400 mt-1">Unidades disponibles</p>
                              </div>
                              <div class="p-2 bg-purple-50 rounded-lg text-purple-500">
                                  <i class="fas fa-shopping-bag text-xl"></i>
                              </div>
                         </div>
                          <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500 flex justify-between items-start">
                              <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Promedio Vistas</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?= number_format((float) ($stats['promedio_vistas'] ?? 0), 1) ?></h3>
                                <p class="text-xs text-yellow-500 mt-1"><i class="fas fa-eye mr-1"></i> Por producto</p>
                              </div>
                              <div class="p-2 bg-green-50 rounded-lg text-green-500">
                                  <i class="fas fa-smile text-xl"></i>
                             </div>
                        </div>
                    </div>

                    <div class="grid lg:grid-cols-3 gap-6">
                        <!-- Chart Area -->
                        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
                             <h3 class="text-lg font-bold text-gray-800 mb-6">Estado del Inventario</h3>
                             <!-- Placeholder for Chart -->
                             <div class="h-64 bg-gray-50 rounded-lg flex items-center justify-center border border-dashed border-gray-300 relative overflow-hidden">
                                   <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-blue-100 to-transparent opacity-50"></div>
                                   <svg class="w-full h-full absolute inset-0 text-blue-400 opacity-30" viewBox="0 0 100 50" preserveAspectRatio="none">
                                       <path d="M0,50 L0,30 Q10,20 20,35 T40,15 T60,25 T80,10 L100,5 L100,50 Z" fill="currentColor" />
                                   </svg>
                                   <div class="text-center text-gray-500 font-medium z-10 space-y-2">
                                       <p><i class="fas fa-boxes-stacked mr-2"></i>Activos: <strong><?= number_format($stats['productos_activos'] ?? 0) ?></strong></p>
                                       <p><i class="fas fa-box-open mr-2"></i>Inactivos: <strong><?= number_format($stats['productos_inactivos'] ?? 0) ?></strong></p>
                                   </div>
                              </div>
                         </div>

                         <!-- Top Products -->
                         <div class="bg-white rounded-xl shadow-sm p-6">
                             <h3 class="text-lg font-bold text-gray-800 mb-6">Más Vistos</h3>
                              <ul class="space-y-4">
                                 <?php if (empty($top_productos)): ?>
                                     <li class="text-sm text-gray-500">Sin productos para mostrar.</li>
                                 <?php else: ?>
                                     <?php foreach ($top_productos as $producto): ?>
                                         <li class="flex items-center justify-between pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                                             <div class="flex items-center space-x-3 min-w-0">
                                                 <div class="w-10 h-10 bg-gray-100 rounded-lg flex-shrink-0 overflow-hidden">
                                                     <img src="<?= BASE_URL . ($producto['imagen'] ?? 'images/default_product.png') ?>" alt="<?= htmlspecialchars($producto['nom_producto'] ?? '') ?>" class="w-full h-full object-cover">
                                                 </div>
                                                 <div class="min-w-0">
                                                     <p class="text-sm font-semibold text-gray-800 truncate"><?= htmlspecialchars($producto['nom_producto'] ?? 'Producto') ?></p>
                                                     <p class="text-xs text-gray-500"><?= number_format($producto['vistas'] ?? 0) ?> vistas</p>
                                                 </div>
                                             </div>
                                             <span class="text-sm font-bold text-tierra-oscuro"><?= number_format($producto['stock_productor'] ?? 0) ?> u.</span>
                                         </li>
                                     <?php endforeach; ?>
                                 <?php endif; ?>
                              </ul>
                         </div>
                     </div>
