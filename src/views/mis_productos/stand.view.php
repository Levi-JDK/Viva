
                    <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                            <div>
                                <h2 class="text-2xl font-bold text-tierra-oscuro">Mi Stand Virtual</h2>
                                <p class="text-gray-500 text-sm">Personaliza cómo los clientes ven tu marca.</p>
                            </div>
                            <?php $stand_url = !empty($stand['id_stand']) ? BASE_URL . 'stand?id=' . $stand['id_stand'] : '#'; ?>
                            <a href="<?= $stand_url ?>" <?= $stand_url === '#' ? 'onclick="return false;" title=\'Guarda tu stand primero\'' : 'target="_blank"' ?> class="text-naranja-artesanal hover:text-tierra-oscuro font-medium text-sm flex items-center border border-naranja-artesanal rounded-full px-4 py-1.5 hover:bg-orange-50 transition-colors">
                                <i class="fas fa-external-link-alt mr-2"></i>Ver página pública
                            </a>
                        </div>
                        <!-- Banner Placeholder -->
                        <div id="banner-placeholder" onclick="document.getElementById('portada-upload').click()" class="h-56 bg-gradient-to-r from-tierra-claro to-beige-suave rounded-xl mb-12 relative group cursor-pointer overflow-hidden border-2 border-dashed border-transparent hover:border-naranja-artesanal transition-all" style="<?= !empty($stand['portada_stand']) ? "background-image: url('" . BASE_URL . $stand['portada_stand'] . "'); background-size: cover; background-position: center;" : '' ?>">
                            <div class="absolute inset-0 flex items-center justify-center bg-black/0 group-hover:bg-black/10 transition-all">
                                <span class="opacity-0 group-hover:opacity-100 bg-white text-gray-800 px-4 py-2 rounded-full shadow-lg font-medium text-sm transition-all transform scale-90 group-hover:scale-100">
                                    <i class="fas fa-camera mr-2"></i>Editar Portada
                                </span>
                            </div>
                        </div>

                        <form id="stand-form" class="space-y-8 relative -mt-20 px-4">
                            <!-- Hidden Inputs for Images (Moved Inside Form) -->
                            <input type="file" id="portada-upload" name="portada_stand" class="hidden" accept="image/*" onchange="previewBackground(this, 'banner-placeholder'); this.form.requestSubmit()">
                            <input type="file" id="logo-upload" name="img_stand" class="hidden" accept="image/*" onchange="previewImage(this, 'stand-logo-img'); this.form.requestSubmit()">

                            <!-- Hidden Stand ID -->
                            <input type="hidden" name="id_stand" value="<?= $stand['id_stand'] ?? '' ?>">
                            
                            <div class="flex flex-col md:flex-row gap-8 items-start">
                                <!-- Logo Upload -->
                                <div class="relative group" onclick="document.getElementById('logo-upload').click()">
                                    <div class="w-32 h-32 bg-white rounded-full p-1 shadow-lg ring-4 ring-white relative z-10 flex-shrink-0 cursor-pointer">
                                        <div class="w-full h-full bg-gray-200 rounded-full overflow-hidden relative group-inner">
                                            <div class="absolute inset-0 flex items-center justify-center bg-black/0 group-hover:bg-black/20 transition-all z-20"> 
                                                <i class="fas fa-camera text-white opacity-0 group-hover:opacity-100 text-2xl drop-shadow-md"></i>
                                            </div>
                                            <img id="stand-logo-img" src="<?= !empty($stand['img_stand']) ? BASE_URL . $stand['img_stand'] : BASE_URL . 'images/default.jpg' ?>" class="w-full h-full object-cover">
                                        </div>
                                    </div>
                                </div>

                                <div class="flex-1 w-full pt-10 md:pt-12 space-y-6">
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre del Emprendimiento</label>
                                            <input type="text" name="nom_stand" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-naranja-artesanal" value="<?= htmlspecialchars($stand['nom_stand'] ?? '') ?>" placeholder="Artesanías Example">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Slogan Corto</label>
                                            <input type="text" name="slogan_stand" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-naranja-artesanal" value="<?= htmlspecialchars($stand['slogan_stand'] ?? '') ?>" placeholder="El arte de nuestras manos">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Historia / Descripción</label>
                                        <textarea name="descripcion_stand" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-naranja-artesanal" placeholder="Cuenta la historia de tu emprendimiento..."><?= htmlspecialchars($stand['descripcion_stand'] ?? '') ?></textarea>
                                    </div>

                                    <div class="flex justify-end pt-4 border-t border-gray-100">
                                        <button type="submit" class="btn-primary text-white px-8 py-2.5 rounded-lg hover:shadow-lg font-medium">Guardar Cambios</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
