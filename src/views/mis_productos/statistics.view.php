
                    <h2 class="text-2xl font-bold text-white mb-6">Resumen de Estadísticas</h2>
                    
                    <!-- KPI Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-naranja-artesanal flex justify-between items-start">
                             <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Ventas Totales</p>
                                <h3 class="text-2xl font-bold text-gray-800">$154,430</h3>
                                <p class="text-xs text-green-500 mt-1"><i class="fas fa-arrow-up mr-1"></i> 13% vs semana pasada</p>
                             </div>
                             <div class="p-2 bg-orange-50 rounded-lg text-naranja-artesanal">
                                 <i class="fas fa-wallet text-xl"></i>
                             </div>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500 flex justify-between items-start">
                             <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Visitas Perfil</p>
                                <h3 class="text-2xl font-bold text-gray-800">6,480</h3>
                                <p class="text-xs text-green-500 mt-1"><i class="fas fa-arrow-up mr-1"></i> 10% vs semana pasada</p>
                             </div>
                             <div class="p-2 bg-blue-50 rounded-lg text-blue-500">
                                 <i class="fas fa-users text-xl"></i>
                             </div>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-purple-500 flex justify-between items-start">
                             <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Pedidos</p>
                                <h3 class="text-2xl font-bold text-gray-800">125</h3>
                                <p class="text-xs text-gray-400 mt-1">Total acumulado</p>
                             </div>
                             <div class="p-2 bg-purple-50 rounded-lg text-purple-500">
                                 <i class="fas fa-shopping-bag text-xl"></i>
                             </div>
                        </div>
                         <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500 flex justify-between items-start">
                             <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Valoración</p>
                                <h3 class="text-2xl font-bold text-gray-800">4.8</h3>
                                <p class="text-xs text-yellow-500 mt-1"><i class="fas fa-star mr-1"></i> Promedio</p>
                             </div>
                             <div class="p-2 bg-green-50 rounded-lg text-green-500">
                                 <i class="fas fa-smile text-xl"></i>
                             </div>
                        </div>
                    </div>

                    <div class="grid lg:grid-cols-3 gap-6">
                        <!-- Chart Area -->
                        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
                             <h3 class="text-lg font-bold text-gray-800 mb-6">Tendencia de Ventas</h3>
                             <!-- Placeholder for Chart -->
                             <div class="h-64 bg-gray-50 rounded-lg flex items-center justify-center border border-dashed border-gray-300 relative overflow-hidden">
                                  <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-blue-100 to-transparent opacity-50"></div>
                                  <svg class="w-full h-full absolute inset-0 text-blue-400 opacity-30" viewBox="0 0 100 50" preserveAspectRatio="none">
                                      <path d="M0,50 L0,30 Q10,20 20,35 T40,15 T60,25 T80,10 L100,5 L100,50 Z" fill="currentColor" />
                                  </svg>
                                  <span class="text-gray-400 font-medium z-10"><i class="fas fa-chart-area mr-2"></i>Gráfico Demostrativo</span>
                             </div>
                        </div>

                        <!-- Top Products -->
                        <div class="bg-white rounded-xl shadow-sm p-6">
                             <h3 class="text-lg font-bold text-gray-800 mb-6">Más Vendidos</h3>
                             <ul class="space-y-4">
                                 <li class="flex items-center justify-between pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                                     <div class="flex items-center space-x-3">
                                         <div class="w-10 h-10 bg-gray-100 rounded-lg flex-shrink-0"></div>
                                         <div>
                                             <p class="text-sm font-semibold text-gray-800">Mochila Wayuu</p>
                                             <p class="text-xs text-gray-500">12 ventas</p>
                                         </div>
                                     </div>
                                     <span class="text-sm font-bold text-tierra-oscuro">$1.2M</span>
                                 </li>
                                 <li class="flex items-center justify-between pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                                     <div class="flex items-center space-x-3">
                                         <div class="w-10 h-10 bg-gray-100 rounded-lg flex-shrink-0"></div>
                                         <div>
                                             <p class="text-sm font-semibold text-gray-800">Sombrero Vueltiao</p>
                                             <p class="text-xs text-gray-500">8 ventas</p>
                                         </div>
                                     </div>
                                     <span class="text-sm font-bold text-tierra-oscuro">$980k</span>
                                 </li>
                                 <li class="flex items-center justify-between pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                                     <div class="flex items-center space-x-3">
                                         <div class="w-10 h-10 bg-gray-100 rounded-lg flex-shrink-0"></div>
                                         <div>
                                             <p class="text-sm font-semibold text-gray-800">Aretes Filigrana</p>
                                             <p class="text-xs text-gray-500">24 ventas</p>
                                         </div>
                                     </div>
                                     <span class="text-sm font-bold text-tierra-oscuro">$1.5M</span>
                                 </li>
                             </ul>
                        </div>
                    </div>
