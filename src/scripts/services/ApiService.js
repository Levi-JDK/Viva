export class ApiService {
    static resolveUrl(url) {
        if (/^https?:\/\//i.test(url)) return url;
        if (typeof window.buildAppUrl === 'function') return window.buildAppUrl(url);
        const base = String(window.BASE_URL || (typeof BASE_URL !== 'undefined' ? BASE_URL : '/') || '/').replace(/\/+$/, '');
        return `${base}/${String(url).replace(/^\/+/, '')}`;
    }

    static async get(url) {
        try {
            const response = await fetch(this.resolveUrl(url), {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = typeof buildAppUrl !== 'undefined' ? buildAppUrl('login') : '/login';
                    return;
                }
                throw new Error(data.mensaje || data.message || `HTTP Error: ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('Service Error:', error);
            throw error;
        }
    }

    static async postJson(url, payload = {}) {
        try {
            const response = await fetch(this.resolveUrl(url), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = typeof buildAppUrl !== 'undefined' ? buildAppUrl('login') : '/login';
                    return;
                }
                throw new Error(data.mensaje || data.message || `HTTP Error: ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('Service Error:', error);
            throw error;
        }
    }

    static async post(url, formData) {
        try {
            const response = await fetch(this.resolveUrl(url), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            if (response.status === 401) {
                window.location.href = typeof buildAppUrl !== 'undefined' ? buildAppUrl('login') : '/login';
                return;
            }
            
            if (!response.ok) {
                throw new Error('HTTP Error: ' + response.status);
            }

            return await response.json();
        } catch (error) {
            console.error('Service Error:', error);
            throw error;
        }
    }
}
