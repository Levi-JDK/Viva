<?php 
$page_title = "VIVA | Iniciar Sesión - Artesanías Colombianas";
$page_description = "Inicia sesión en VIVA para comprar artesanías colombianas y gestionar tu cuenta.";
$body_class = "flex flex-col min-h-screen overflow-x-hidden font-sans text-oscuro bg-fondo-claro";
$extra_css = '<link rel="prefetch" href="' . (defined('BASE_URL') ? BASE_URL : '/') . 'registro">';
require_once __DIR__ . '/partials/base_head.php'; 
require_once __DIR__ . "/partials/header.php"; 
?>
	<!-- Toast Container -->
	<div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3"></div>

	<main class="center flex-1 w-full flex items-center justify-center py-10 md:py-20 px-4 bg-cover bg-center bg-fixed bg-no-repeat" style="background-image: url('<?= BASE_URL ?>images/artesanias_full.webp');">
		<div class="container flex flex-col md:flex-row bg-fondo-claro rounded-2xl shadow-2xl overflow-hidden w-full max-w-[min(90%,50rem)] min-h-[37.5rem] md:min-h-[34.375rem] transform-gpu will-change-transform" id="auth-shell" data-auth-shell>
			
			<!-- Login Form (left side) -->
			<div class="w-full md:w-1/2 flex flex-col items-center justify-center px-8 py-10 transform-gpu will-change-transform" data-auth-panel="login">
				<form id="form-login" data-action="login" method="POST" class="flex flex-col items-center justify-center text-center w-full">
					<input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? '', ENT_QUOTES) ?>">
					<h1 class="font-bold text-3xl mb-4 text-oscuro">Iniciar Sesión</h1>
					<input type="email" name="email" placeholder="Email" required class="bg-fondo-oscuro border-none p-3 my-3 w-full rounded text-sm focus:outline-none focus:bg-gray-200 transition-colors" />
					<div class="relative w-full">
						<input type="password" name="contrasena" id="login-pass" placeholder="Contraseña" required class="bg-fondo-oscuro border-none p-3 my-3 w-full rounded text-sm focus:outline-none focus:bg-gray-200 transition-colors pr-10" />
						<button type="button" data-action="toggle-password" data-target="login-pass" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-oscuro focus:outline-none" tabindex="-1" aria-label="Mostrar contraseña">
							<i class="fa fa-eye"></i>
						</button>
					</div>
					<a href="<?= BASE_URL ?>recuperar" class="text-oscuro text-sm no-underline my-4 hover:text-principal transition-colors">¿Olvidaste tu contraseña?</a>

					<button type="submit" class="rounded-full border border-principal bg-principal text-white text-xs font-bold py-3 px-10 uppercase tracking-wider transition-transform transform hover:bg-secundario hover:-translate-y-0.5 active:scale-95 focus:outline-none mt-6 cursor-pointer">Iniciar Sesión</button>

					<!-- Botón para móviles -->
					<div class="mobile-switch md:hidden mt-6 pt-4 border-t border-gray-200 w-full">
						<p class="text-sm text-oscuro">¿No tienes cuenta? <a href="<?= BASE_URL ?>registro" class="text-tierra-medio font-bold hover:text-naranja-artesanal hover:underline">Registrarse</a></p>
					</div>
				</form>
			</div>

			<!-- Overlay Panel (right side - desktop only) -->
			<div class="hidden md:flex md:w-1/2 bg-gradient-to-r from-claro to-principal text-white flex-col items-center justify-center p-10 text-center transform-gpu will-change-transform" data-auth-overlay="login">
				<h1 class="font-bold text-3xl mb-4 text-white">¡Hola, Amigo!</h1>
				<p class="text-sm leading-5 tracking-wide my-5 text-white">Descubre una artesanía que cuente nuestra historia.</p>
				<a href="<?= BASE_URL ?>registro" class="ghost rounded-full border border-white bg-transparent text-white text-xs font-bold py-3 px-10 uppercase tracking-wider transition-transform transform hover:bg-white/10 hover:-translate-y-0.5 active:scale-95 focus:outline-none cursor-pointer inline-block">Registrarse</a>
			</div>
		</div>
	</main>
	<!-- <?php require_once 'partials/footer_login.php'; ?> -->

	<script type="module" src="<?= BASE_URL ?>src/scripts/controllers/AuthController.js"></script>
</body>
</html>
