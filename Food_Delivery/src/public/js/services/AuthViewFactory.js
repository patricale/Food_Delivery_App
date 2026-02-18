// SVOLTO DA: SALE MARIO
// MATRICOLA: 364432

import { LoginView } from '../view/LoginView.js';      
import { RegisterUniPRView } from '../view/RegisterUniPRView.js';

export class AuthViewFactory {
    static createView(type) {
        console.log("AuthViewFactory: Richiesta creazione vista per:", type);
        
        switch (type) {
            case 'login':
                console.log("AuthViewFactory: Restituisco LoginView");
                return new LoginView();
            case 'register':
                console.log("AuthViewFactory: Restituisco RegisterUniPRView");
                return new RegisterUniPRView();
            default:
                console.error("AuthViewFactory: Tipo vista non supportato:", type);
                throw new Error('Tipo di vista non supportato: ' + type);
        }
    }
}