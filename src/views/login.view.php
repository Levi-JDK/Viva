<?php 
$page_title = "VIVA | Iniciar Sesión - Artesanías Colombianas";
$body_class = "flex flex-col min-h-screen font-sans text-oscuro bg-fondo-claro";
require_once __DIR__ . '/partials/base_head.php'; 
require_once __DIR__ . "/partials/header.php"; 
?>
	<!-- Toast Container -->
	<div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3"></div>

	<div class="center flex-1 w-full flex items-center justify-center py-10 md:py-20 px-4 bg-cover bg-center bg-fixed bg-no-repeat" style="background-image: url('<?= BASE_URL ?>images/artesanias.png');">
		<div class="container relative bg-fondo-claro rounded-2xl shadow-2xl overflow-hidden w-full max-w-[768px] min-h-[600px] md:min-h-[550px]" id="container">
			
			<!-- Sign Up Container -->
			<div class="form-container sign-up-container absolute top-0 left-0 h-full w-full md:w-1/2 opacity-0 z-1 md:opacity-0 md:z-1">
				<form id="form-registro" method="POST" class="bg-fondo-claro flex flex-col items-center justify-center h-full px-8 md:px-12 text-center">
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
						<button type="button" onclick="togglePassword('reg-pass', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-oscuro focus:outline-none" tabindex="-1" aria-label="Mostrar contraseña">
							<i class="fa fa-eye"></i>
						</button>
					</div>
					<button type="submit" class="rounded-full border border-principal bg-principal text-white text-xs font-bold py-3 px-10 uppercase tracking-wider transition-transform transform hover:bg-secundario hover:-translate-y-0.5 active:scale-95 focus:outline-none mt-6 cursor-pointer">Registrarse</button>
					
					<!-- Botón para móviles -->
					<div class="mobile-switch md:hidden mt-6 pt-4 border-t border-gray-200 w-full">
						<p class="text-sm text-oscuro">¿Ya tienes cuenta? <a href="#" id="signIn-mobile" class="text-tierra-medio font-bold hover:text-naranja-artesanal hover:underline">Iniciar Sesión</a></p>
					</div>
				</form>
			</div>

			<!-- Sign In Container -->
			<div class="form-container sign-in-container absolute top-0 left-0 h-full w-full md:w-1/2 z-2">
				<form id="form-login" method="POST" class="bg-fondo-claro flex flex-col items-center justify-center h-full px-8 md:px-12 text-center">
					<input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? '', ENT_QUOTES) ?>">
					<h1 class="font-bold text-3xl mb-4 text-oscuro">Iniciar Sesión</h1>
					<div class="social-container my-4">
						<a href="#" class="social border border-gray-300 rounded-full inline-flex justify-center items-center w-10 h-10 mx-1 hover:bg-gray-100 transition-colors"><i class="fab fa-google-plus-g text-oscuro"></i></a>
					</div>
					<span class="text-xs mb-4 text-gray-500">o usa tu cuenta</span>
					<input type="email" name="email" placeholder="Email" required class="bg-fondo-oscuro border-none p-3 my-3 w-full rounded text-sm focus:outline-none focus:bg-gray-200 transition-colors" />
					<div class="relative w-full">
						<input type="password" name="contrasena" id="login-pass" placeholder="Contraseña" required class="bg-fondo-oscuro border-none p-3 my-3 w-full rounded text-sm focus:outline-none focus:bg-gray-200 transition-colors pr-10" />
						<button type="button" onclick="togglePassword('login-pass', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-oscuro focus:outline-none" tabindex="-1" aria-label="Mostrar contraseña">
							<i class="fa fa-eye"></i>
						</button>
					</div>
					<a href="<?= BASE_URL ?>recuperar" class="text-oscuro text-sm no-underline my-4 hover:text-principal transition-colors">¿Olvidaste tu contraseña?</a>

					<button type="submit" class="rounded-full border border-principal bg-principal text-white text-xs font-bold py-3 px-10 uppercase tracking-wider transition-transform transform hover:bg-secundario hover:-translate-y-0.5 active:scale-95 focus:outline-none mt-6 cursor-pointer">Iniciar Sesión</button>

					<!-- Botón para móviles -->
					<div class="mobile-switch md:hidden mt-6 pt-4 border-t border-gray-200 w-full">
						<p class="text-sm text-oscuro">¿No tienes cuenta? <a href="#" id="signUp-mobile" class="text-tierra-medio font-bold hover:text-naranja-artesanal hover:underline">Registrarse</a></p>
					</div>
				</form>
			</div>

			<!-- Overlay Container -->
			<div class="overlay-container hidden md:block absolute top-0 left-1/2 w-1/2 h-full overflow-hidden z-100">
				<div class="overlay bg-gradient-to-r from-claro to-principal text-white relative -left-full h-full w-[200%] transform translate-x-0">
					<div class="overlay-panel overlay-left absolute flex flex-col items-center justify-center px-10 text-center top-0 h-full w-1/2 transform -translate-x-[20%]">
						<h1 class="font-bold text-3xl mb-4 text-oscuro">¡Bienvenido de nuevo!</h1>
						<p class="text-sm leading-5 tracking-wide my-5 text-oscuro">Para mantenerte conectado con nosotros, por favor inicia sesión con tu información personal</p>
						<button class="ghost rounded-full border border-oscuro bg-transparent text-oscuro text-xs font-bold py-3 px-10 uppercase tracking-wider transition-transform transform hover:bg-white/10 hover:-translate-y-0.5 active:scale-95 focus:outline-none cursor-pointer" id="signIn">Iniciar Sesión</button>
					</div>
					<div class="overlay-panel overlay-right absolute right-0 flex flex-col items-center justify-center px-10 text-center top-0 h-full w-1/2 transform">
						<h1 class="font-bold text-3xl mb-4 text-white">¡Hola, Amigo!</h1>
						<p class="text-sm leading-5 tracking-wide my-5 text-white">Descubre una artesanía que cuente nuestra historia.</p>
						<button class="ghost rounded-full border border-white bg-transparent text-white text-xs font-bold py-3 px-10 uppercase tracking-wider transition-transform transform hover:bg-white/10 hover:-translate-y-0.5 active:scale-95 focus:outline-none cursor-pointer" id="signUp">Registrarse</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- <?php require_once 'partials/footer_login.php'; ?> -->
	<script src="<?= BASE_URL ?>src/scripts/toast.js"></script>
	<script src="<?= BASE_URL ?>src/scripts/login-ui.js"></script>
	<script src="<?= BASE_URL ?>src/scripts/auth.js"></script>
	<script>
		function togglePassword(inputId, btn) {
			const input = document.getElementById(inputId);
			const icon  = btn.querySelector('i');
			if (input.type === 'password') {
				input.type = 'text';
				icon.classList.replace('fa-eye', 'fa-eye-slash');
			} else {
				input.type = 'password';
				icon.classList.replace('fa-eye-slash', 'fa-eye');
			}
		}
	</script>
</body>
</html>
