<?php 
$page_title = "VIVA | Crear Cuenta - Artesanías Colombianas";
$body_class = "flex flex-col min-h-screen overflow-x-hidden font-sans text-oscuro bg-fondo-claro";
$extra_css = '<link rel="prefetch" href="' . (defined('BASE_URL') ? BASE_URL : '/') . 'login">';
require_once __DIR__ . '/partials/base_head.php'; 
require_once __DIR__ . "/partials/header.php"; 
?>
	<!-- Toast Container -->
	<div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3"></div>

	<main class="center flex-1 w-full flex items-center justify-center py-10 md:py-20 px-4 bg-cover bg-center bg-fixed bg-no-repeat" style="background-image: url('<?= BASE_URL ?>images/artesanias_full.webp');">
		<div class="container flex flex-col md:flex-row bg-fondo-claro rounded-2xl shadow-2xl overflow-hidden w-full max-w-[min(90%,50rem)] min-h-[37.5rem] md:min-h-[34.375rem] transform-gpu will-change-transform" id="auth-shell" data-auth-shell>
			
			<!-- Overlay Panel (left side - desktop only) -->
			<div class="hidden md:flex md:w-1/2 bg-gradient-to-r from-claro to-principal text-white flex-col items-center justify-center p-10 text-center transform-gpu will-change-transform" data-auth-overlay="register">
				<h1 class="font-bold text-3xl mb-4 text-white">¡Bienvenido de nuevo!</h1>
				<p class="text-sm leading-5 tracking-wide my-5 text-white">Para mantenerte conectado con nosotros, por favor inicia sesión con tu información personal</p>
				<a href="<?= BASE_URL ?>login" class="ghost rounded-full border border-white bg-transparent text-white text-xs font-bold py-3 px-10 uppercase tracking-wider transition-transform transform hover:bg-white/10 hover:-translate-y-0.5 active:scale-95 focus:outline-none cursor-pointer inline-block">Iniciar Sesión</a>
			</div>

			<!-- Register Form (right side) -->
			<div class="w-full md:w-1/2 flex flex-col items-center justify-center px-8 py-10 transform-gpu will-change-transform" data-auth-panel="register">
				<form id="form-registro" data-action="register" method="POST" class="flex flex-col items-center justify-center text-center w-full">
					<input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? '', ENT_QUOTES) ?>">
					<h1 class="font-bold text-3xl mb-4 text-oscuro">Crear Cuenta</h1>
					<div class="social-container my-4">
						<a href="#" class="social border border-gray-300 rounded-full inline-flex justify-center items-center w-10 h-10 mx-1 hover:bg-gray-100 transition-colors"><i class="fab fa-google-plus-g text-oscuro"></i></a>
					</div>
					<span class="text-xs mb-4 text-gray-500">o usa tu email para registrarte</span>
					<input type="text" name="nombre" placeholder="Nombre" required class="bg-fondo-oscuro border-none p-3 my-3 w-full rounded text-sm focus:outline-none focus:bg-gray-200 transition-colors" />
					<input type="text" name="apellido" placeholder="Apellido" required class="bg-fondo-oscuro border-none p-3 my-3 w-full rounded text-sm focus:outline-none focus:bg-gray-200 transition-colors" />
					<input type="email" name="email" placeholder="Email" required class="bg-fondo-oscuro border-none p-3 my-3 w-full rounded text-sm focus:outline-none focus:bg-gray-200 transition-colors" />
					<div class="relative w-full">
						<input type="password" name="contrasena" id="reg-pass" placeholder="Contraseña" required class="bg-fondo-oscuro border-none p-3 my-3 w-full rounded text-sm focus:outline-none focus:bg-gray-200 transition-colors pr-10" />
						<button type="button" data-action="toggle-password" data-target="reg-pass" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-oscuro focus:outline-none" tabindex="-1" aria-label="Mostrar contraseña">
							<i class="fa fa-eye"></i>
						</button>
					</div>
					<button type="submit" class="rounded-full border border-principal bg-principal text-white text-xs font-bold py-3 px-10 uppercase tracking-wider transition-transform transform hover:bg-secundario hover:-translate-y-0.5 active:scale-95 focus:outline-none mt-6 cursor-pointer">Registrarse</button>
					
					<!-- Botón para móviles -->
					<div class="mobile-switch md:hidden mt-6 pt-4 border-t border-gray-200 w-full">
						<p class="text-sm text-oscuro">¿Ya tienes cuenta? <a href="<?= BASE_URL ?>login" class="text-tierra-medio font-bold hover:text-naranja-artesanal hover:underline">Iniciar Sesión</a></p>
					</div>
				</form>
			</div>
		</div>
	</main>
	<!-- <?php require_once 'partials/footer_login.php'; ?> -->

	<script type="module" src="<?= BASE_URL ?>src/scripts/controllers/AuthController.js"></script>
</body>
</html>
