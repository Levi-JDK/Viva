export class UserMenuController {
    init() {
        this.userDropdown = document.getElementById('userDropdown');

        if (!this.userDropdown) return;

        document.addEventListener('click', (e) => {
            const userMenuBtn = document.getElementById('userMenuBtn');
            if (userMenuBtn && !userMenuBtn.contains(e.target) && !this.userDropdown.contains(e.target)) {
                this.closeDropdown();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeDropdown();
            }
        });
    }

    toggleDropdown(e) {
        if (e) e.stopPropagation();
        if (!this.userDropdown) return;
        const isVisible = !this.userDropdown.classList.contains('invisible');
        if (isVisible) {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }

    openDropdown() {
        if (!this.userDropdown) return;
        this.userDropdown.classList.remove('opacity-0', 'invisible', 'scale-95');
        this.userDropdown.classList.add('opacity-100', 'visible', 'scale-100');
    }

    closeDropdown() {
        if (!this.userDropdown) return;
        this.userDropdown.classList.add('opacity-0', 'invisible', 'scale-95');
        this.userDropdown.classList.remove('opacity-100', 'visible', 'scale-100');
    }

    toggleMobileMenu() {
        document.getElementById('mobileMenu')?.classList.toggle('hidden');
    }
}
export const userMenuController = new UserMenuController();