<footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Logo and Description -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-tierra-oscuro to-verde-artesanal rounded-lg flex items-center justify-center">
                            <!-- object-cover llena el contenedor del logo sin deformarlo. -->
                            <img src="<?= base_url_path('images/Logo_thumb.webp') ?>" alt="VIVA" class="w-full h-full object-cover" loading="lazy">
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">VIVA</h3>
                            <p class="text-sm text-gray-400">Artesanías Colombianas</p>
                        </div>
                    </div>
                    <p class="text-gray-300 mb-4 max-w-md">
                        Conectando tradiciones milenarias con el mundo moderno. 
                        Apoyamos a las comunidades indígenas colombianas a través del comercio justo.
                    </p>

                </div>
                <!-- Quick Links -->
                <div>
                    <h4 class="font-semibold text-lg mb-4">Enlaces rápidos</h4>
                    <ul class="space-y-2">
                        <li><a href="<?= base_url_path('politica_privacidad') ?>" target="_blank" class="text-gray-300 hover:text-naranja-artesanal transition-colors">Políticas de privacidad</a></li>
                        <li><a href="<?= base_url_path('terminos_condiciones') ?>" target="_blank" class="text-gray-300 hover:text-naranja-artesanal transition-colors">Términos y condiciones</a></li>

                    </ul>
                </div>

            </div>
            <!-- Bottom Footer -->
            <div class="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">
                    © 2025 VIVA Artesanías Colombianas. Todos los derechos reservados.
                </p>
                <div class="flex items-center space-x-4 mt-4 md:mt-0">
                    <span class="text-gray-400 text-sm">Métodos de pago:</span>
                    <div class="flex space-x-2">
                        <div class="w-8 h-6 bg-gray-700 rounded flex items-center justify-center ">
                            <i class="fab fa-cc-visa text-xs"></i>
                        </div>
                        <div class="w-8 h-6 bg-gray-700 rounded flex items-center justify-center">
                            <i class="fab fa-cc-mastercard text-xs"></i>
                        </div>
                        <div class="w-8 h-6 bg-gray-700 rounded flex items-center justify-center">
                            <i class="fab fa-cc-paypal text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
