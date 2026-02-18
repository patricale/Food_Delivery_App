// SVOLTO DA: SALE MARIO
// MATRICOLA: 364432

import { IAuthView } from './IAuthView.js';

export class RegisterUniPRView extends IAuthView {
    constructor() {
        super(); 
        this.app = document.getElementById('app');
        this.render();
    }

    getHtml() {
        return `
            <style>
                .field-transition {
                    overflow: hidden;
                    max-height: 120px;
                    opacity: 1;
                    transition: all 0.4s ease-in-out;
                }
                .field-hidden {
                    max-height: 0 !important;
                    opacity: 0 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                /* Stile per errore campo specifico */
                .field-error {
                    color: #dc3545;
                    font-size: 0.875em;
                    margin-top: 0.25rem;
                    display: none;
                }
                .is-invalid {
                    border-color: #dc3545 !important;
                }
                .is-invalid:focus {
                    border-color: #dc3545 !important;
                    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
                }
                /* Aggiungo solo classi per i bottoni se mancano in global.css */
                .btn-success { background-color: #198754; border-color: #198754; }
            </style>

            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body p-4">
                                <h3 class="card-title text-center mb-4">Registrazione Campus</h3>
                                <div id="register-error" class="alert alert-danger d-none"></div>
                                
                                <form id="registerForm">
                                    <div class="mb-3">
                                        <label class="form-label">Nome</label>
                                        <input type="text" name="nome" id="nome" class="form-control" required>
                                        <div class="field-error" id="nome-error"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Cognome</label>
                                        <input type="text" name="cognome" id="cognome" class="form-control" required>
                                        <div class="field-error" id="cognome-error"></div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Email Istituzionale</label>
                                        <input type="email" id="emailInput" name="email" class="form-control" placeholder="nome.cognome@studenti.unipr.it" required>
                                        <div class="form-text">Usa @studenti.unipr.it (Studenti) o @unipr.it (Docenti)</div>
                                        <div class="field-error" id="email-error"></div>
                                    </div>

                                    <div class="mb-3 field-transition" id="matricolaContainer">
                                        <label class="form-label">Matricola</label>
                                        <input type="text" name="matricola" id="matricolaInput" class="form-control">
                                        <div class="field-error" id="matricola-error"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password" id="password" class="form-control" required minlength="8" maxlength="72">
                                        <div class="field-error" id="password-error"></div>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">Registrati</button>
                                </form>
                                <div class="text-center mt-3">
                                    <a href="?view=login" class="btn btn-link">Torna al Login</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    render() {
        if (this.app) {
            this.app.innerHTML = this.getHtml();
            this.attachListeners();
        }
    }

    attachListeners() {
        const emailInput = document.getElementById('emailInput');
        const matricolaContainer = document.getElementById('matricolaContainer');
        const matricolaInput = document.getElementById('matricolaInput');

        if(emailInput) {
            emailInput.addEventListener('input', (e) => {
                const val = e.target.value.toLowerCase();
                
                if (val.includes('@s')) {
                    matricolaContainer.classList.remove('field-hidden');
                    matricolaInput.setAttribute('required', 'required');
                } 
                else if (val.includes('@u')) {
                    matricolaContainer.classList.add('field-hidden');
                    matricolaInput.removeAttribute('required');
                    
                    setTimeout(() => {
                        if(matricolaContainer.classList.contains('field-hidden')) {
                            matricolaInput.value = ''; 
                        }
                    }, 400);
                }
                else {
                    matricolaContainer.classList.remove('field-hidden');
                }
            });
        }
    }

    bindSubmit(handler) {
        const form = document.getElementById('registerForm');
        if (form) {
            form.addEventListener('submit', e => {
                e.preventDefault();
                
                this.clearFieldErrors();
                
                const passwordField = document.getElementById('password');
                const password = passwordField.value;
                
                if (password.length > 0 && password.length < 8) {
                    this.showFieldError('password', 'La password deve essere di almeno 8 caratteri.');
                    passwordField.focus();
                    return;
                }
                
                if (password.length > 72) {
                    this.showFieldError('password', 'La password non può superare 72 caratteri.');
                    passwordField.focus();
                    return;
                }
                
                const formData = new FormData(e.target);
                const data = Object.fromEntries(formData.entries());
                
                handler(data);
            });
        }
    }

    showFieldError(fieldName, message) {
        const field = document.getElementById(fieldName);
        const errorDiv = document.getElementById(fieldName + '-error');
        
        if (field && errorDiv) {
            field.classList.add('is-invalid');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    }

    clearFieldErrors() {
        const errorDivs = document.querySelectorAll('.field-error');
        const invalidFields = document.querySelectorAll('.is-invalid');
        
        errorDivs.forEach(div => {
            div.textContent = '';
            div.style.display = 'none';
        });
        
        invalidFields.forEach(field => {
            field.classList.remove('is-invalid');
        });
        
        const alert = document.getElementById('register-error');
        if (alert) {
            alert.classList.add('d-none');
        }
    }

    showFieldErrors(errors) {
        this.clearFieldErrors();
        
        Object.keys(errors).forEach(fieldName => {
            this.showFieldError(fieldName, errors[fieldName]);
        });
    }

    showError(msg) {
        const alert = document.getElementById('register-error');
        if(alert) {
            alert.textContent = msg;
            alert.classList.remove('d-none');
            
            alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            alert(msg);
        }
    }
    
    attachPasswordValidation() {
        const passwordField = document.getElementById('password');
        if (passwordField) {
            passwordField.addEventListener('input', () => {
                const password = passwordField.value;
                const errorDiv = document.getElementById('password-error');
                
                if (password.length > 0 && password.length < 8) {
                    this.showFieldError('password', 'La password deve essere di almeno 8 caratteri.');
                } else if (password.length > 72) {
                    this.showFieldError('password', 'La password non può superare 72 caratteri.');
                } else if (password.length >= 8 && password.length <= 72) {
                    passwordField.classList.remove('is-invalid');
                    errorDiv.style.display = 'none';
                }
            });
        }
    }
}