export class LoginUIController {
    init() {
        this.signUpButton = document.getElementById('signUp');
        this.signInButton = document.getElementById('signIn');
        this.container = document.getElementById('container');
        this.signUpMobile = document.getElementById('signUp-mobile');
        this.signInMobile = document.getElementById('signIn-mobile');

        this.bindEvents();
        this.initializeMobileView();
        window.addEventListener('resize', this.initializeMobileView.bind(this));
    }

    bindEvents() {
        if (this.signUpButton) {
            this.signUpButton.addEventListener('click', () => this.showSignUp());
        }

        if (this.signInButton) {
            this.signInButton.addEventListener('click', () => this.showSignIn());
        }

        if (this.signUpMobile) {
            this.signUpMobile.addEventListener('click', (e) => {
                e.preventDefault();
                this.showSignUp();
            });
        }

        if (this.signInMobile) {
            this.signInMobile.addEventListener('click', (e) => {
                e.preventDefault();
                this.showSignIn();
            });
        }
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