export class LandingController {
    init() {
        // Remove fake cart logic. The "add to cart" buttons now use data-action="add-cart".
        // Keep UI behaviors like mobile menu, scroll, animations.
        
        // Expose toggleMobileMenu for inline calls
        window.toggleMobileMenu = this.toggleMobileMenu.bind(this);
        window.scrollToSection = this.scrollToSection.bind(this);

        this.initMobileMenuLinks();
        this.initScrollToTop();
        this.initFadeAnimations();
        
        window.addEventListener("scroll", this.handleScroll.bind(this));
    }

    toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        if (menu) {
            menu.classList.toggle('hidden');
            if (!menu.classList.contains('hidden')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
    }

    initMobileMenuLinks() {
        document.querySelectorAll("#mobileMenu a").forEach(link => {
            link.addEventListener("click", () => {
                const menu = document.getElementById("mobileMenu");
                if (menu) menu.classList.add("hidden");
                document.body.style.overflow = "";
            });
        });
    }

    scrollToSection(sectionId) {
        const element = document.getElementById(sectionId);
        if (element) {
            element.scrollIntoView({ behavior: "smooth", block: "start" });
        }
    }

    initScrollToTop() {
        this.scrollToTopBtn = document.getElementById("scrollToTop");
        if (this.scrollToTopBtn) {
            this.scrollToTopBtn.addEventListener("click", () => {
                window.scrollTo({ top: 0, behavior: "smooth" });
            });
        }
    }

    handleScroll() {
        if (this.scrollToTopBtn) {
            if (window.scrollY > 150) {
                this.scrollToTopBtn.classList.remove("opacity-0", "invisible");
                this.scrollToTopBtn.classList.add("opacity-100", "visible");
            } else {
                this.scrollToTopBtn.classList.add("opacity-0", "invisible");
                this.scrollToTopBtn.classList.remove("opacity-100", "visible");
            }
        }

        const header = document.querySelector("header");
        if (header) {
            if (!header.dataset.lastScroll) header.dataset.lastScroll = 0;
            if (window.scrollY > header.dataset.lastScroll && window.scrollY > 100) {
                header.style.transform = "translateY(-100%)";
            } else {
                header.style.transform = "translateY(0)";
            }
            header.dataset.lastScroll = window.scrollY;
        }
    }

    initFadeAnimations() {
        const observerOptions = { threshold: 0.1 };
        const fadeInObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("visible");
                }
            });
        }, observerOptions);

        document.querySelectorAll(".fade-in").forEach(el => {
            fadeInObserver.observe(el);
        });
    }
}
export const landingController = new LandingController();