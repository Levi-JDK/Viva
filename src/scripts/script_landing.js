// Tailwind Configuration for Custom Colors
tailwind.config = {
    theme: {
        extend: {
            colors: {
                'tierra-oscuro': '#8B4513',
                'tierra-medio': '#CD853F',
                'tierra-claro': '#DEB887',
                'verde-artesanal': '#6B8E23',
                'naranja-artesanal': '#D2691E',
                'beige-suave': '#F5F5DC'
            },
            fontFamily: {
                'sans': ['Inter', 'system-ui', 'sans-serif']
            }
        }
    }
};

// ===== CART FUNCTIONALITY =====
let cart = [];

function toggleCart() {
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    const isOpen = !cartSidebar.classList.contains('translate-x-full');

    if (isOpen) {
        // Cerrar carrito
        cartSidebar.classList.add('translate-x-full');
        cartOverlay.classList.add('opacity-0', 'invisible');
        cartOverlay.classList.remove('opacity-50', 'visible');
        document.body.style.overflow = '';
    } else {
        // Abrir carrito
        cartSidebar.classList.remove('translate-x-full');
        cartOverlay.classList.remove('opacity-0', 'invisible');
        cartOverlay.classList.add('opacity-50', 'visible');
        document.body.style.overflow = 'hidden';
    }
}

function addToCart(productName, price) {
    // Buscar si el producto ya existe en el carrito
    const existingProduct = cart.find(item => item.name === productName);

    if (existingProduct) {
        existingProduct.quantity += 1;
    } else {
        cart.push({
            name: productName,
            price: price,
            quantity: 1
        });
    }

    updateCart();

    // Mostrar feedback visual
    showCartNotification(`${productName} agregado al carrito`);
}

function removeFromCart(productName) {
    cart = cart.filter(item => item.name !== productName);
    updateCart();
}

function updateQuantity(productName, change) {
    const product = cart.find(item => item.name === productName);
    if (product) {
        product.quantity += change;
        if (product.quantity <= 0) {
            removeFromCart(productName);
        } else {
            updateCart();
        }
    }
}

function updateCart() {
    const cartItems = document.getElementById('cartItems');
    const cartCount = document.getElementById('cartCount');
    const cartTotal = document.getElementById('cartTotal');

    // Actualizar contador del badge
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = totalItems;

    // Si el carrito está vacío
    if (cart.length === 0) {
        cartItems.innerHTML = '<li class="text-center text-gray-500 py-8">Tu carrito está vacío</li>';
        cartTotal.textContent = '$0';
        return;
    }

    // Renderizar items del carrito
    cartItems.innerHTML = cart.map(item => `
        <li class="border-b pb-3 last:border-b-0">
            <div class="flex justify-between items-start mb-2">
                <div class="flex-1">
                    <h4 class="font-semibold text-tierra-oscuro text-sm">${item.name}</h4>
                    <p class="text-xs text-gray-600">$${item.price.toLocaleString()}</p>
                </div>
                <button onclick="removeFromCart('${item.name}')" class="text-red-500 hover:text-red-700 ml-2">
                    <i class="fas fa-trash text-xs"></i>
                </button>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2 bg-gray-100 rounded-full px-2 py-1">
                    <button onclick="updateQuantity('${item.name}', -1)" class="w-6 h-6 flex items-center justify-center hover:bg-gray-200 rounded-full transition-colors">
                        <i class="fas fa-minus text-xs text-gray-600"></i>
                    </button>
                    <span class="text-sm font-medium px-2">${item.quantity}</span>
                    <button onclick="updateQuantity('${item.name}', 1)" class="w-6 h-6 flex items-center justify-center hover:bg-gray-200 rounded-full transition-colors">
                        <i class="fas fa-plus text-xs text-gray-600"></i>
                    </button>
                </div>
                <span class="font-bold text-tierra-oscuro">$${(item.price * item.quantity).toLocaleString()}</span>
            </div>
        </li>
    `).join('');

    // Calcular y mostrar total
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    cartTotal.textContent = `$${total.toLocaleString()}`;
}

function showCartNotification(message) {
    // Crear notificación temporal
    const notification = document.createElement('div');
    notification.className = 'fixed top-20 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center space-x-2 animate-slide-in';
    notification.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
    `;

    document.body.appendChild(notification);

    // Remover después de 3 segundos
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// ===== MOBILE MENU FUNCTIONALITY =====
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');

    // Cierra el scroll detrás del menú (mejor UX en mobile)
    if (!menu.classList.contains('hidden')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}

// ===== LOGIN REDIRECT =====
function redirectToLogin() {
    // Ajusta la ruta según tu proyecto
    window.location.href = "login.html";
}

// ===== SCROLL TO SECTION =====
function scrollToSection(sectionId) {
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ behavior: "smooth", block: "start" });
    }
}

// ===== SCROLL TO TOP =====
const scrollToTopBtn = document.getElementById("scrollToTop");

function handleScroll() {
    // Mostrar/ocultar botón "scroll to top"
    if (window.scrollY > 150) {
        scrollToTopBtn.classList.remove("opacity-0", "invisible");
        scrollToTopBtn.classList.add("opacity-100", "visible");
    } else {
        scrollToTopBtn.classList.add("opacity-0", "invisible");
        scrollToTopBtn.classList.remove("opacity-100", "visible");
    }

    // Ocultar/mostrar header al hacer scroll
    const header = document.querySelector("header");
    if (!header.dataset.lastScroll) header.dataset.lastScroll = 0;

    if (window.scrollY > header.dataset.lastScroll && window.scrollY > 100) {
        header.style.transform = "translateY(-100%)";
    } else {
        header.style.transform = "translateY(0)";
    }

    header.dataset.lastScroll = window.scrollY;
}

if (scrollToTopBtn) {
    scrollToTopBtn.addEventListener("click", () => {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });
}

// ===== FADE-IN ANIMATIONS =====
const observerOptions = { threshold: 0.1 };
const fadeInObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add("visible");
        }
    });
}, observerOptions);

// ===== INIT =====
document.addEventListener("DOMContentLoaded", () => {
    // Activar animaciones fade-in
    document.querySelectorAll(".fade-in").forEach(el => {
        fadeInObserver.observe(el);
    });

    // Cerrar menú móvil al hacer clic en links
    document.querySelectorAll("#mobileMenu a").forEach(link => {
        link.addEventListener("click", () => {
            document.getElementById("mobileMenu").classList.add("hidden");
            document.body.style.overflow = "";
        });
    });

    // Configurar botón del carrito
    const cartBtn = document.getElementById('cartBtn');
    if (cartBtn) {
        cartBtn.addEventListener('click', toggleCart);
    }

    // Cerrar carrito al hacer clic en el overlay
    const cartOverlay = document.getElementById('cartOverlay');
    if (cartOverlay) {
        cartOverlay.addEventListener('click', toggleCart);
    }
});

// Listener de scroll optimizado
window.addEventListener("scroll", handleScroll);