// SVOLTO DA: SALE MARIO
// MATRICOLA: 364432

import { IAuthView } from './IAuthView.js';

export class LoginView extends IAuthView {
    constructor() {
        super(); 
        this.app = document.getElementById('app');
        console.log("LoginView inizializzata"); 
        this.render();
    }

    getHtml() {
        return `
        <main class="flex-grow-1 d-flex align-items-center justify-content-center w-100">
            <div class="text-center" style="max-width: 400px; width: 100%;">

                <!-- LOGO SOSTITUITO AL POSTO DI "FDC" -->
                <div class="mb-3">
                    <img src="assets/logo.gif" alt="Logo Food Delivery Campus" 
                        style="
                            width: 110px;
                            height: auto;
                            border-radius: 12px;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                        ">
                </div>

                <h1 class="h3 fw-bold">Food Delivery Campus</h1>
                
                <div id="login-error" class="alert alert-danger d-none mt-3 text-start"></div>

                <div class="fdc-card p-4 text-start mt-4">
                    <form id="loginForm">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn-fdc w-100">ACCEDI</button>
                    </form>

                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="small text-muted mb-2">Non hai un account?</p>
                        <a href="?view=register" class="btn btn-link text-decoration-none p-0 fw-bold" 
                           style="color: var(--primary-color, #0d6efd);">
                            Registrati come Cliente UniPR
                        </a>
                    </div>
                </div>
            </div>
        </main>
        `;
    }

    render() {
        if (this.app) {
            this.app.innerHTML = this.getHtml();
        } else {
            console.error("ERRORE: Elemento #app non trovato!");
        }
    }

    bindSubmit(handler) {
        const form = document.getElementById('loginForm');
        if (form) {
            form.addEventListener('submit', e => {
                e.preventDefault();
                const emailInput = document.getElementById('email');
                const passwordInput = document.getElementById('password');
                
                if(emailInput && passwordInput) {
                     handler({ 
                        email: emailInput.value, 
                        password: passwordInput.value 
                    });
                }
            });
        } else {
            console.error("ERRORE: Form #loginForm non trovato nel DOM!");
        }
    }

    showError(msg) {
        const alert = document.getElementById('login-error');
        if (alert) {
            alert.textContent = msg;
            alert.classList.remove('d-none');
        } else {
            window.alert(msg);
        }
    }
}
