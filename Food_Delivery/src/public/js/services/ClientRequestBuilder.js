//SVOLTO DA:SALE MARIO
//MATRICOLA:364432

export class ClientRequestBuilder {
    constructor() {
        this.url = '';
        this.method = 'GET';
        this.headers = {
            'Content-Type': 'application/json'
        };
        this.body = null;
    }

    setUrl(url) {
        this.url = url;
        return this;
    }

    setMethod(method) {
        this.method = method;
        return this;
    }

    withAuthToken() {
        const token = localStorage.getItem('jwt_token');
        if (token) {
            this.headers['Authorization'] = 'Bearer ' + token;
        }
        return this;
    }

    setBody(data) {
        this.body = JSON.stringify(data);
        return this;
    }

    async build() {
        const options = {
            method: this.method,
            headers: this.headers
        };

        if (this.body && this.method !== 'GET') {
            options.body = this.body;
        }

        try {
            const response = await fetch(this.url, options);
            
            if (!response.ok) {
                const errJson = await response.json().catch(() => ({}));
                throw new Error(errJson.message || 'Errore di connessione al server');
            }

            return await response.json();
        } catch (error) {
            throw error;
        }
    }
}