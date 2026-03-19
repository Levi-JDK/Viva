
                    <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                        <div class="flex flex-col sm:flex-row items-center justify-between mb-6 gap-4">
                            <h2 class="text-xl font-bold text-tierra-oscuro">Inventario</h2>
                            <div class="flex space-x-3 w-full sm:w-auto">
                                <div class="relative flex-1 sm:flex-none">
                                    <input type="text" placeholder="Buscar..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-naranja-artesanal w-full">
                                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                                </div>
                                <a href="?view=add_product" class="btn-primary text-white px-4 py-2 rounded-lg text-sm hover:shadow-lg transition-all flex items-center">
                                    <i class="fas fa-plus mr-2"></i>Nuevo
                                </a>
                            </div>
                        </div>

                        <!-- Product Grid -->
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            <?php if (empty($productos)): ?>
                                <div class="col-span-full text-center py-10 text-gray-500">
                                    <i class="fas fa-box-open text-4xl mb-3 opacity-50"></i>
                                    <p>No tienes productos registrados aún.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($productos as $producto): ?>
                                    <div class="group relative bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden">
                                        
                                        <!-- Badge Status -->
                                        <div class="absolute top-3 right-3 z-10">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold backdrop-blur-md shadow-sm <?= $producto['activo'] ? 'bg-green-100/90 text-green-700' : 'bg-gray-100/90 text-gray-600' ?>">
                                                <?= $producto['activo'] ? 'Activo' : 'Inactivo' ?>
                                            </span>
                                        </div>

                                        <!-- Image Container -->
                                        <div class="relative h-56 overflow-hidden bg-gray-100">
                                            <?php if (!empty($producto['imagen'])): ?>
                                                <img src="<?= BASE_URL . $producto['imagen'] ?>" 
                                                     alt="<?= htmlspecialchars($producto['nom_producto']) ?>" 
                                                     loading="lazy"
                                                     class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                    <i class="fas fa-image text-5xl"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Quick Actions Overlay -->
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-3">
                                                <button data-action="edit-product" data-product-id="<?= $producto['id_producto'] ?>" class="p-2 bg-white rounded-full text-gray-700 hover:text-blue-600 hover:scale-110 transition-all shadow-lg" title="Editar">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                                <button data-action="delete-product" data-product-id="<?= $producto['id_producto'] ?>" class="p-2 bg-white rounded-full text-gray-700 hover:text-red-600 hover:scale-110 transition-all shadow-lg" title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Content -->
                                        <div class="p-5">
                                            <div class="mb-2">
                                                <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-md">
                                                    <?= htmlspecialchars($producto['nom_categoria']) ?>
                                                </span>
                                            </div>
                                            
                                            <h3 class="text-lg font-bold text-gray-800 mb-1 leading-tight line-clamp-1" title="<?= htmlspecialchars($producto['nom_producto']) ?>">
                                                <?= htmlspecialchars($producto['nom_producto']) ?>
                                            </h3>
                                            
                                            <div class="flex items-end justify-between mt-4">
                                                <div>
                                                    <p class="text-xs text-gray-500 mb-0.5">Precio</p>
                                                    <p class="text-xl font-extrabold text-tierra-oscuro">
                                                        $<?= number_format($producto['precio_producto'], 0, ',', '.') ?>
                                                    </p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-xs text-gray-500 mb-0.5">Stock</p>
                                                    <p class="text-sm font-semibold <?= $producto['stock_productor'] > 0 ? 'text-gray-700' : 'text-red-500' ?>">
                                                        <?= $producto['stock_productor'] ?> u.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
