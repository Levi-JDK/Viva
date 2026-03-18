<?php
$page_title = "VIVA | Recuperar Contraseña";
$body_class = "flex flex-col min-h-screen font-sans text-oscuro bg-fondo-claro";
require_once __DIR__ . '/partials/base_head.php';
require_once __DIR__ . "/partials/header.php";
?>

<div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3"></div>

<div class="flex-1 flex items-center justify-center py-16 px-4 bg-cover bg-center"
     style="background-image: url('<?= BASE_URL ?>images/artesanias.png');">

    <div class="bg-fondo-claro rounded-2xl shadow-2xl w-full max-w-md p-8 md:p-10">

        <!-- Paso 1 → Email (visible al iniciar) -->
        <div id="paso-email">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full mb-4">
                    <i class="fas fa-lock text-naranja-artesanal text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-tierra-oscuro">Recuperar contraseña</h1>
                <p class="text-gray-500 text-sm mt-2">Ingresa tu correo y te enviamos un código de verificación.</p>
            </div>

            <form id="form-solicitar" class="space-y-7">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Correo electrónico</label>
                    <input type="email" name="email" id="rec-email" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-naranja-artesanal text-sm"
                           placeholder="tucorreo@ejemplo.com">
                </div>
                <button type="submit"
                        class="w-full btn-primary text-white py-3 rounded-lg font-semibold text-sm hover:shadow-lg transition-all">
                    Enviar código
                </button>
            </form>
            <p class="text-center text-sm text-gray-500 mt-6">
                ¿Recordaste tu contraseña? <a href="<?= BASE_URL ?>login" class="text-naranja-artesanal font-semibold hover:underline">Iniciar sesión</a>
            </p>
        </div>

        <!-- Paso 2 → Código + Nueva contraseña (oculto al inicio) -->
        <div id="paso-codigo" class="hidden">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                    <i class="fas fa-envelope-open-text text-green-600 text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-tierra-oscuro">Revisa tu correo</h1>
                <p class="text-gray-500 text-sm mt-2">Ingresa el código de 6 dígitos que te enviamos y tu nueva contraseña.</p>
            </div>

            <form id="form-confirmar" class="space-y-7">
                <input type="hidden" name="email" id="rec-email-confirm">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Código de verificación</label>
                    <input type="text" name="token" id="rec-token" required maxlength="6" pattern="\d{6}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-naranja-artesanal text-sm text-center tracking-[0.5em] text-xl font-bold"
                           placeholder="000000">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nueva contraseña</label>
                    <div class="relative">
                        <input type="password" name="pass_nueva" id="rec-pass" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-naranja-artesanal text-sm pr-10"
                               placeholder="Mínimo 8 caracteres">
                        <button type="button" onclick="togglePassword('rec-pass', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-oscuro" tabindex="-1">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Confirmar contraseña</label>
                    <div class="relative">
                        <input type="password" name="pass_confirmacion" id="rec-pass-conf" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-naranja-artesanal text-sm pr-10"
                               placeholder="Repite la contraseña">
                        <button type="button" onclick="togglePassword('rec-pass-conf', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-oscuro" tabindex="-1">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full btn-primary text-white py-3 rounded-lg font-semibold text-sm hover:shadow-lg transition-all">
                    Cambiar contraseña
                </button>
            </form>

            <button onclick="volverAlPaso1()" class="mt-4 text-sm text-gray-500 hover:text-naranja-artesanal flex items-center gap-2 mx-auto">
                <i class="fas fa-arrow-left text-xs"></i> Volver e ingresar otro correo
            </button>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>src/scripts/recuperar.js"></script>
</body>
</html>
