
                    <div class="max-w-4xl mx-auto items-center justify-center">
                        <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                            <?php
                                $is_edit = isset($producto_editar);
                                $prod = $is_edit ? $producto_editar : [];
                            ?>
                            <form id="product-upload-form" data-action="submit-product" class="space-y-8" data-mode="<?= $is_edit ? 'edit' : 'create' ?>">
                                <?php if ($is_edit): ?>
                                    <input type="hidden" name="id_producto" value="<?= $producto_editar['id_producto'] ?>">
                                    <script>
                                        window.existingImages = <?= $producto_editar['imagenes'] ?? '[]' ?>;
                                    </script>
                                <?php endif; ?>
                                <!-- Image Upload Area -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-3">Imágenes del Producto (Máx 4)</label>
                                    
                                    <!-- Hidden Input for File Selection -->
                                    <input type="file" id="product-images-input" data-action="product-images-change" name="imagen_producto[]" accept="image/*" multiple class="hidden">

                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="image-preview-grid">
                                        <!-- Content rendered via JS (dash_productos.js) -->
                                        <!-- Initial State: Button + 3 Placeholders -->
                                        <div id="add-image-btn-placeholder" data-event="click:triggerImageUpload" class="border-2 border-dashed border-naranja-artesanal/30 rounded-lg aspect-square flex flex-col items-center justify-center text-center hover:bg-orange-50 transition-colors cursor-pointer bg-orange-50/30 relative overflow-hidden group">
                                            <i class="fas fa-plus text-2xl text-naranja-artesanal mb-2 group-hover:scale-110 transition-transform"></i>
                                            <span class="text-xs text-naranja-artesanal font-medium">Agregar</span>
                                        </div>
                                        
                                        <div class="preview-slot border-2 border-dashed border-gray-200 rounded-lg aspect-square flex items-center justify-center bg-gray-50 opacity-50">
                                            <i class="fas fa-image text-gray-300"></i>
                                        </div>
                                        <div class="preview-slot border-2 border-dashed border-gray-200 rounded-lg aspect-square flex items-center justify-center bg-gray-50 opacity-50">
                                            <i class="fas fa-image text-gray-300"></i>
                                        </div>
                                        <div class="preview-slot border-2 border-dashed border-gray-200 rounded-lg aspect-square flex items-center justify-center bg-gray-50 opacity-50">
                                            <i class="fas fa-image text-gray-300"></i>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-2">Formatos: JPG, PNG, WEBP. Máx 5MB por imagen.</p>
                                </div>

                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre del Producto</label>
                                            <input type="text" name="nom_producto" id="miInput" data-event="keypress:misc-keypress" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-naranja-artesanal focus:ring-1 focus:ring-naranja-artesanal" placeholder="Ej: Mochila Arhuaca" value="<?= htmlspecialchars($producto_editar['nom_producto'] ?? '') ?>" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Precio (COP)</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-2 text-gray-500">$</span>
                                                <input type="number" data-event="keydown:numeric-keydown, input:numeric-input" name="precio_producto" min="1" step="1" class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-naranja-artesanal focus:ring-1 focus:ring-naranja-artesanal" placeholder="0" value="<?= isset($producto_editar['precio_producto']) ? (int) $producto_editar['precio_producto'] : '' ?>" required>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Stock Disponible</label>
                                            <input type="number" data-event="keydown:numeric-keydown, input:numeric-input" name="stock_productor" min="0" step="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-naranja-artesanal focus:ring-1 focus:ring-naranja-artesanal" placeholder="1" value="<?= $producto_editar['stock_productor'] ?? '' ?>" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Materia Prima</label>
                                            <select name="id_materia" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-naranja-artesanal focus:ring-1 focus:ring-naranja-artesanal bg-white" required>
                                                <option value="">Seleccionar materia prima...</option>
                                                <?php foreach ($materias as $materia): ?>
                                                    <option value="<?= $materia['id_materia'] ?>" <?= ($producto_editar['id_materia'] ?? '') == $materia['id_materia'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($materia['nom_materia']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Categoría</label>
                                            <select name="id_categoria" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-naranja-artesanal focus:ring-1 focus:ring-naranja-artesanal bg-white" required>
                                                <option value="">Seleccionar categoría...</option>
                                                <?php foreach ($categorias as $categoria): ?>
                                                    <option value="<?= $categoria['id_categoria'] ?>" <?= ($producto_editar['id_categoria'] ?? '') == $categoria['id_categoria'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($categoria['nom_categoria']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Color Principal</label>
                                            <select name="id_color" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-naranja-artesanal focus:ring-1 focus:ring-naranja-artesanal bg-white" required>
                                                <option value="">Seleccionar color...</option>
                                                <?php foreach ($colores as $color): ?>
                                                    <option value="<?= $color['id_color'] ?>" <?= ($producto_editar['id_color'] ?? '') == $color['id_color'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($color['nom_color']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Oficio</label>
                                            <select name="id_oficio" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-naranja-artesanal focus:ring-1 focus:ring-naranja-artesanal bg-white" required>
                                                <option value="">Seleccionar oficio...</option>
                                                <?php foreach ($oficios as $oficio): ?>
                                                    <option value="<?= $oficio['id_oficio'] ?>" <?= ($producto_editar['id_oficio'] ?? '') == $oficio['id_oficio'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($oficio['nom_oficio']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción Detallada</label>
                                    <textarea name="desc_prod_personal" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-naranja-artesanal focus:ring-1 focus:ring-naranja-artesanal" placeholder="Describe los materiales, técnica, historia..." required><?= htmlspecialchars($producto_editar['descripcion_producto'] ?? '') ?></textarea>
                                </div>
                                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-100">
                                    <a href="?view=inventory" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 font-medium transition-colors">Cancelar</a>
                                    <button type="submit" class="btn-primary text-white px-8 py-2.5 rounded-lg hover:shadow-lg font-medium transition-all text-center">
                                        <?= $is_edit ? 'Guardar Cambios' : 'Publicar Producto' ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
