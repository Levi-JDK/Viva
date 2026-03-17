<header class="bg-white/95 backdrop-blur-sm shadow-lg py-4 sticky top-0 z-50">
        <div class="container mx-auto px-4 md:px-6">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="<?= BASE_URL ?>" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 bg-gradient-to-br from-tierra-oscuro to-verde-artesanal rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-all duration-300 group-hover:scale-105">
                        <img src="<?= BASE_URL ?>images/Logo.png" alt="VIVA" class="w-full h-full object-contain rounded-xl">
                    </div>
                    <div>
                        <h1 class="text-xl font-extrabold text-tierra-oscuro group-hover:text-tierra-medio transition-colors">VIVA</h1>
                        <p class="text-xs text-tierra-medio font-medium">Artesanías Colombianas</p>
                    </div>
                </a>
                
                <!-- Botón Volver Mejorado -->
                <a href="<?= BASE_URL ?>" 
                   style="background: linear-gradient(135deg, #CD853F, #D2691E);"
                   class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-full 
                          text-white font-semibold text-sm 
                          shadow-md hover:shadow-xl 
                          transform hover:-translate-y-0.5 hover:scale-105 
                          transition-all duration-300 ease-out
                          border border-white/20">
                    <i class="fas fa-arrow-left text-sm"></i>
                    <span>Volver</span>
                </a>
            </div>
        </div>
    </header>