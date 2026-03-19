export class UserMenuController {
    init() {
        this.userMenuBtn = document.getElementById('userMenuBtn');
        this.userDropdown = document.getElementById('userDropdown');

        if (!this.userMenuBtn || !this.userDropdown) return;

        this.userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleDropdown();
        });

        document.addEventListener('click', (e) => {
            if (!this.userMenuBtn.contains(e.target) && !this.userDropdown.contains(e.target)) {
                this.closeDropdown();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeDropdown();
            }
        });
    }

    toggleDropdown() {
        const isVisible = !this.userDropdown.classList.contains('invisible');
        if (isVisible) {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }

    openDropdown() {
        this.userDropdown.classList.remove('opacity-0', 'invisible', 'scale-95');
        this.userDropdown.classList.add('opacity-100', 'visible', 'scale-100');
    }

    closeDropdown() {
        this.userDropdown.classList.add('opacity-0', 'invisible', 'scale-95');
        this.userDropdown.classList.remove('opacity-100', 'visible', 'scale-100');
    }
}
export const userMenuController = new UserMenuController();