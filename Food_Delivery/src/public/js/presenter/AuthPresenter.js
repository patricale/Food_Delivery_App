// SVOLTO DA: SALE MARIO
// MATRICOLA: 364432

import { AuthModel } from '../model/AuthModel.js';
import { AuthViewFactory } from '../services/AuthViewFactory.js';
import { LoginView } from '../view/LoginView.js';

export class AuthPresenter {
    constructor(viewType) {
        console.log("AuthPresenter: Inizializzazione con tipo:", viewType);
        
        this.model = new AuthModel();
        
        try {
            this.view = AuthViewFactory.createView(viewType);
            this.init();
        } catch (e) {
            console.error("AuthPresenter Errore critico:", e);
        }
    }

    init() {
        if (this.view) {
            console.log("AuthPresenter: Binding evento submit");
            this.view.bindSubmit(this.handleAuth.bind(this));
            
            if (this.view.attachPasswordValidation) {
                this.view.attachPasswordValidation();
            }
        } else {
            console.error("AuthPresenter: Vista non inizializzata, impossibile fare bind.");
        }
    }

    async handleAuth(data) {
        console.log("AuthPresenter: Tentativo auth con dati:", data);
        
        try {
            let result;
            
            if (this.view instanceof LoginView) {
                result = await this.model.login(data.email, data.password);
            } else {
                result = await this.model.register(data);
            }

            console.log("AuthPresenter: Risultato API:", result);

            if (result.token) {
                localStorage.setItem('jwt_token', result.token);
                localStorage.setItem('user_role', result.ruolo);
                localStorage.setItem('user_id', result.id_utente);
                
                if (result.ruolo === 'esercente') {
                    window.location.href = 'Esercente.html';
                } else {
                    window.location.href = 'Cliente.html';
                }
            } else if (result.errors) {
                console.log("Errori di validazione:", result.errors);
                
                if (this.view.showFieldErrors) {
                    this.view.showFieldErrors(result.errors);
                    
                    const firstField = Object.keys(result.errors)[0];
                    const fieldElement = document.getElementById(firstField);
                    if (fieldElement) {
                        fieldElement.focus();
                    }
                } else {
                    const firstError = Object.values(result.errors)[0];
                    this.view.showError(firstError);
                }
            } else if (result.message) {
                const msg = result.message.toLowerCase();
                if (msg.includes("successo") || msg.includes("created") || msg.includes("benvenuto")) {
                    this.view.showSuccess(result.message);
                    setTimeout(() => {
                        window.location.href = 'index.html';
                    }, 2000);
                } else {
                    this.view.showError(result.message);
                }
            } else {
                this.view.showError("Errore sconosciuto dal server.");
            }

        } catch (error) {
            console.error("AuthPresenter Errore Auth:", error);
            const errMsg = error.message || "Errore di connessione al server.";
            this.view.showError(errMsg);
        }
    }
}