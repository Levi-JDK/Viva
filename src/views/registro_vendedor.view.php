<?php 
$page_title = "Registro de Vendedor - VIVA";
$body_class = "flex flex-col min-h-screen font-sans text-oscuro bg-fondo-claro";
require_once __DIR__ . '/partials/base_head.php'; 
?>
    <?php include_once ROOT_PATH . "src/views/partials/header.php"; ?>
    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3"></div>
    
    <!-- Wrapper do background -->
    <div class="flex-1 w-full relative bg-cover bg-center bg-fixed bg-no-repeat" style="background-image: url('<?= BASE_URL ?>images/background_registro_vender.webp');">
        <!-- Overlay oscuro para resaltar el texto blanco -->
        <div class="absolute inset-0 bg-black/40"></div>
        <!-- Contenedor Principal -->
        <div class="container mx-auto px-4 py-4 relative z-10">
        <!-- Título y Descripción -->
        <div class="text-center mb-4">
            <br>
            <h2 class="text-2xl md:text-3xl font-bold text-white mb-2 drop-shadow-lg">
                Conviértete en Vendedor
            </h2>
            <p class="text-white text-sm drop-shadow">
                Únete a nuestra comunidad de artesanos
            </p>
        </div>

        <!-- Indicadores de Pasos -->
        <div class="max-w-3xl mx-auto mb-4">
            <div class="flex items-center justify-between relative px-4">
                <!-- Línea de progreso -->
                <div class="progress-line">
                    <div id="progressBar" class="progress-line-fill" style="width: 0%"></div>
                </div>
                
                <!-- Step 1 -->
                <div class="flex flex-col items-center relative z-10">
                    <div class="step-indicator active" data-step="1">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="text-xs mt-1 font-semibold text-white drop-shadow">Información</span>
                </div>
                
                <!-- Step 2 -->
                <div class="flex flex-col items-center relative z-10">
                    <div class="step-indicator" data-step="2">
                        <i class="fas fa-university"></i>
                    </div>
                    <span class="text-xs mt-1 font-medium text-white/80 drop-shadow">Datos personales</span>
                </div>
                
                <!-- Step 3 -->
                <div class="flex flex-col items-center relative z-10">
                    <div class="step-indicator" data-step="3">
                        <i class="fas fa-check"></i>
                    </div>
                    <span class="text-xs mt-1 font-medium text-white/80 drop-shadow">Confirmar</span>
                </div>
            </div>
        </div>

        <!-- Formulario -->
         <br>
        <div class="max-w-4xl mx-auto form-container p-5 md:p-6">
            <form id="vendorRegistrationForm">
                
                <!-- PASO 1: Información Personal -->
                <div class="form-step active" data-step="1">
                    <h3 class="text-xl font-bold text-oscuro mb-4">Información Personal</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Tipo de Documento -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Tipo de Documento <span class="text-red-500">*</span>
                            </label>
                            <select name="tipo_documento" required 
                                    class="w-full px-3 py-2 text-sm bg-fondo-oscuro border-none rounded-lg transition-all">
                                <?php 
                                    foreach ($tipos_doc as $tipos) {
                                        ?>
                                         <option value="<?=$tipos['id']?>"><?=$tipos['nombre']?></option>
                                <?php }?>
                            </select>
                        </div>

                        <!-- Número de Documento -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Número de Documento <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="numero_documento" required
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   pattern="\d{10}" 
                                   title="Deben ser exactamente 10 números" 
                                   maxlength="10"
                                   class="w-full px-3 py-2 text-sm bg-fondo-oscuro border-none rounded-lg transition-all"
                                   placeholder="10043659858">
                        </div>

                        <!-- Dirección -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Dirección <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="direccion" required
                                   class="w-full px-3 py-2 text-sm bg-fondo-oscuro border-none rounded-lg transition-all"
                                   placeholder="Ej: Calle 123 #45-67">
                        </div>

                        <!-- Departamento -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Departamento <span class="text-red-500">*</span>
                            </label>
                            <select name="departamento" id="departamento" required 
                                    class="w-full px-3 py-2 text-sm bg-fondo-oscuro border-none rounded-lg transition-all">
                                <option value="" disabled selected>Seleccione un departamento...</option>
                                <?php 
                                    foreach ($departamentos as $departamento) {
                                        ?>
                                         <option value="<?=$departamento['id']?>"><?=$departamento['nombre']?></option>
                                <?php }?>
                            </select>
                        </div>

                            <!-- Ciudad -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Ciudad <span class="text-red-500">*</span>
                            </label>
                            <select name="ciudad" id="ciudad" required disabled
                                    class="w-full px-3 py-2 text-sm bg-fondo-oscuro border-none rounded-lg transition-all">
                                <option value="">Seleccione un departamento...</option>
                            </select>
                        </div>

                        <!-- Grupo/Comunidad -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Grupo o Comunidad Artesanal <span class="text-red-500">*</span>
                            </label>
                            <select name="grupo_artesanal" required 
                                    class="w-full px-3 py-2 text-sm bg-fondo-oscuro border-none rounded-lg transition-all">
                                 <option value="" disabled selected>Seleccione una comunidad...</option>
                                 <?php foreach ($grupos as $grupo){?>
                                    <option value="<?=$grupo['id']?>"><?=$grupo['nombre']?></option>
                                 <?php } ?>
                            </select>
                        </div>

                        <!-- Aceptación de términos -->
                        <div class="md:col-span-2 space-y-3 mt-4">
                            <label class="flex items-start space-x-3 cursor-pointer group">
                                <input type="checkbox" name="acepta_terminos" required
                                       class="mt-0.5 w-4 h-4 rounded focus:ring-2 focus:ring-principal">
                                <span class="text-xs text-gray-700 group-hover:text-oscuro">
                                    Acepto los <a href="<?= BASE_URL ?>terminos_condiciones" target="_blank" class="text-principal font-semibold hover:underline">Términos y Condiciones</a> de VIVA
                                </span>
                            </label>
                            
                            <label class="flex items-start space-x-2 cursor-pointer group">
                                <input type="checkbox" name="acepta_tratamiento_datos" required
                                       class="mt-0.5 w-4 h-4 rounded focus:ring-2 focus:ring-principal">
                                <span class="text-xs text-gray-700 group-hover:text-oscuro">
                                    Autorizo el <a href="<?= BASE_URL ?>politica_privacidad" target="_blank" class="text-principal font-semibold hover:underline">Tratamiento de mis Datos</a> según la ley 1581 de 2012
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Botones de Navegación -->
                    <div class="flex justify-end mt-5">
                        <button type="button" onclick="nextStep()" 
                                class="btn-gradient text-white px-10 py-3 rounded-full font-bold text-sm uppercase tracking-wider">
                            Siguiente <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>

                <!-- PASO 2: Información Bancaria -->
                 <br>
                <div class="form-step" data-step="2">
                    <h3 class="text-xl font-bold text-oscuro mb-6">Información personal</h3>
                    <p class="text-gray-600 mb-6">Esta información es necesaria para recibir los pagos de tus ventas</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Banco -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Banco <span class="text-red-500">*</span>
                            </label>
                            <select name="banco" required 
                                    class="w-full px-3 py-2 text-sm bg-fondo-oscuro border-none rounded-lg transition-all">
                                <option value="" disabled selected>Seleccionar banco...</option>
                                <?php foreach ($bancos as $banco){?>
                                <option value="<?=$banco['id']?>"><?=$banco['nombre']?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <!-- Tipo de Cuenta -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Tipo de Cuenta <span class="text-red-500">*</span>
                            </label>
                            <select name="tipo_cuenta" required 
                                    class="w-full px-3 py-2 text-sm bg-fondo-oscuro border-none rounded-lg transition-all">
                                <option value="">Seleccionar...</option>
                                <option value="Ahorros">Ahorros</option>
                                <option value="Corriente">Corriente</option>
                            </select>
                        </div>

                        <!-- Número de Cuenta -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Número de Cuenta <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="numero_cuenta" required
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   pattern="\d+"
                                   title="Solo se permiten números"
                                   maxlength="12"
                                   class="w-full px-3 py-2 text-sm bg-fondo-oscuro border-none rounded-lg transition-all"
                                   placeholder="Ej: 1234567890">
                        </div>

                    </div>

                    <!-- Botones de Navegación -->
                    <div class="flex justify-end mt-5">
                        <button type="button" onclick="prevStep()" 
                                class="text-gray-600 hover:text-oscuro px-6 py-2 rounded-full font-bold text-sm uppercase tracking-wide transition-all mr-auto">
                            <i class="fas fa-arrow-left mr-2"></i> Anterior
                        </button>
                        <button type="button" onclick="nextStep()" 
                                class="btn-gradient text-white px-10 py-3 rounded-full font-bold text-sm uppercase tracking-wider">
                            Siguiente <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>

                <!-- PASO 3: Confirmación -->
                <div class="form-step" data-step="3">
                    <h3 class="text-xl font-bold text-oscuro mb-2 -mt-6">Confirmar Datos</h3>
                    <p class="text-gray-600 mb-4 text-sm">Por favor verifica que la información sea correcta antes de enviar.</p>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-4">
                        <!-- Resumen Personal -->
                        <div>
                            <h4 class="text-xs font-bold text-principal uppercase tracking-wider mb-2 border-b border-gray-100 pb-1">
                                <i class="fas fa-user mr-2"></i> Información Personal
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                                <div>
                                    <span class="block text-gray-500">Tipo de Documento</span>
                                    <span id="summary-tipo-doc" class="font-medium text-gray-800"></span>
                                </div>
                                <div>
                                    <span class="block text-gray-500">Número de Documento</span>
                                    <span id="summary-num-doc" class="font-medium text-gray-800"></span>
                                </div>
                                <div class="md:col-span-2">
                                    <span class="block text-gray-500">Dirección</span>
                                    <span id="summary-direccion" class="font-medium text-gray-800"></span>
                                </div>
                                <div>
                                    <span class="block text-gray-500">Departamento</span>
                                    <span id="summary-departamento" class="font-medium text-gray-800"></span>
                                </div>
                                <div>
                                    <span class="block text-gray-500">Ciudad</span>
                                    <span id="summary-ciudad" class="font-medium text-gray-800"></span>
                                </div>
                                <div class="md:col-span-2">
                                    <span class="block text-gray-500">Grupo Artesanal</span>
                                    <span id="summary-grupo" class="font-medium text-gray-800"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen Bancario -->
                        <div>
                            <h4 class="text-xs font-bold text-principal uppercase tracking-wider mb-2 border-b border-gray-100 pb-1">
                                <i class="fas fa-university mr-2"></i> Información Bancaria
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                                <div class="md:col-span-2">
                                    <span class="block text-gray-500">Banco</span>
                                    <span id="summary-banco" class="font-medium text-gray-800"></span>
                                </div>
                                <div>
                                    <span class="block text-gray-500">Tipo de Cuenta</span>
                                    <span id="summary-tipo-cuenta" class="font-medium text-gray-800"></span>
                                </div>
                                <div>
                                    <span class="block text-gray-500">Número de Cuenta</span>
                                    <span id="summary-num-cuenta" class="font-medium text-gray-800"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex justify-between mt-8">
                        <button type="button" onclick="prevStep()" 
                                class="text-gray-600 hover:text-oscuro px-6 py-2 rounded-full font-bold text-sm uppercase tracking-wide transition-all">
                            <i class="fas fa-arrow-left mr-2"></i> Volver a editar
                        </button>
                        <button type="submit" 
                                class="btn-gradient text-white px-8 py-3 rounded-full font-bold text-sm uppercase tracking-wide shadow-lg transform hover:-translate-y-0.5 transition-all">
                            Confirmar y Enviar <i class="fas fa-check-circle ml-2"></i>
                        </button>
                    </div>
                </div>

            </form>
        </div>

        <!-- Mensaje de ayuda -->
        <div class="max-w-4xl mx-auto mt-6 text-center">
            <p class="text-sm text-white drop-shadow">
                ¿Necesitas ayuda? <a href="#" class="font-semibold hover:underline">Contáctanos</a>
            </p>
        </div>
    </div>
    </div>

    
    <script src="<?= BASE_URL ?>src/scripts/toast.js"></script>
    <script src="<?= BASE_URL ?>src/scripts/consultar_ciudades.js"></script>
    <script src="<?= BASE_URL ?>src/scripts/registro_vendedor.js"></script>
</body>
</html>