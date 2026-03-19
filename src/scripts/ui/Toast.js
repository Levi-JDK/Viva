export class Toast {
    static getToastContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-5 right-5 z-50 flex flex-col gap-3';
            document.body.appendChild(container);
        }
        return container;
    }

    static show(message, type = 'success') {
        const toastContainer = this.getToastContainer();
        const toast = document.createElement('div');

        let bgColor, icon;
        if (type === 'success') {
            bgColor = 'bg-green-500';
            icon = '<i class="fas fa-check-circle"></i>';
        } else if (type === 'error') {
            bgColor = 'bg-red-500';
            icon = '<i class="fas fa-exclamation-circle"></i>';
        } else {
            bgColor = 'bg-blue-500';
            icon = '<i class="fas fa-info-circle"></i>';
        }

        toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 min-w-[300px] toast-enter`;
        toast.innerHTML = `
            <div class="text-xl">${icon}</div>
            <div class="font-medium text-sm">${message}</div>
        `;

        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('toast-enter');
            toast.classList.add('toast-exit');

            const removeTimeout = setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 600);

            toast.addEventListener('animationend', () => {
                clearTimeout(removeTimeout);
                if (toast.parentElement) {
                    toast.remove();
                }
            });
        }, 3000);
    }
}