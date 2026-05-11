
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



                      <div id="producer-dashboard-stats" class="grid lg:grid-cols-2 gap-6 mt-6">
                          <div class="bg-white rounded-xl shadow-sm p-6">
                              <h3 class="text-lg font-bold text-gray-800 mb-1">Ingresos últimos 30 días</h3>
                              <p class="text-xs text-gray-500 mb-6">Ventas del productor autenticado</p>
                              <div class="h-72"><canvas id="producer-revenue-sales-chart"></canvas></div>
                          </div>
                          <div class="bg-white rounded-xl shadow-sm p-6">
                              <div class="flex items-center justify-between gap-4 mb-6">
                                  <div>
                                      <h3 class="text-lg font-bold text-gray-800 mb-1">Top productos vendidos</h3>
                                      <p class="text-xs text-gray-500">Top 3 por unidades facturadas</p>
                                  </div>
                                  <p id="producer-dashboard-stats-error" class="text-xs font-semibold text-red-500"></p>
                              </div>
                              <div class="h-72"><canvas id="producer-top-products-chart"></canvas></div>
                          </div>
                      </div>

                      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                      <script type="module" src="<?= BASE_URL ?>src/scripts/producer_stats.js"></script>
