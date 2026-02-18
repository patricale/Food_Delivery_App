// SVOLTO DA: SALE MARIO
// MATRICOLA: 364432

import { ClientRequestBuilder } from '../services/ClientRequestBuilder.js';

export class AuthModel {
    constructor() {
        this.apiBaseUrl = 'http://localhost:8001/auth';
    }

    async login(email, password) {
        return new ClientRequestBuilder()
            .setUrl(`${this.apiBaseUrl}/login.php`)
            .setMethod('POST')
            .setBody({ email, password })
            .build();
    }

    async register(data) {
        return new ClientRequestBuilder()
            .setUrl(`${this.apiBaseUrl}/register.php`)
            .setMethod('POST')
            .setBody(data)
            .build();
    }
}