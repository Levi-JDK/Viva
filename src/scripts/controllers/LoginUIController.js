export class LoginUIController {
    init() {
        this.container = document.getElementById('container');
        this.initializeMobileView();
        window.addEventListener('resize', this.initializeMobileView.bind(this));
    }

    showSignUp() {
        if (!this.container) return;
        this.container.classList.add("right-panel-active");
        this.container.classList.add("mobile-show-register");
        this.container.classList.remove("mobile-show-login");
    }

    showSignIn() {
        if (!this.container) return;
        this.container.classList.remove("right-panel-active");
        this.container.classList.add("mobile-show-login");
        this.container.classList.remove("mobile-show-register");
    }

    initializeMobileView() {
        if (window.innerWidth <= 768 && this.container) {
            if (!this.container.classList.contains('mobile-show-register')) {
                this.showSignIn();
            }
        }
    }
}
export const loginUIController = new LoginUIController();