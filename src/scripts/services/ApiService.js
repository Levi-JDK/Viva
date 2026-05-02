export class ApiService {
    static async post(url, formData) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
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