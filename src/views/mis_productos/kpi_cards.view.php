
                   <!-- KPI Small Cards for Products -->
                   <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-orange-100 flex items-center">
                            <div class="p-3 bg-orange-100 text-naranja-artesanal rounded-full mr-4">
                                <i class="fas fa-box text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Total Productos</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?= number_format($total_productos ?? 0) ?></h3>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-green-100 flex items-center">
                            <div class="p-3 bg-green-100 text-verde-artesanal rounded-full mr-4">
                                <i class="fas fa-check-circle text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Activos</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?= number_format($productos_activos ?? 0) ?></h3>
                            </div>
                        </div>
                         <div class="bg-white p-6 rounded-xl shadow-sm border border-blue-100 flex items-center">
                            <div class="p-3 bg-blue-100 text-blue-600 rounded-full mr-4">
                                <i class="fas fa-eye text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Vistas Totales</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?= number_format($vistas_totales ?? 0) ?></h3>
                            </div>
                        </div>
                   </div>
