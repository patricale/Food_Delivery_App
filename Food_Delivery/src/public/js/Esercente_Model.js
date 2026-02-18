// Autore: Andrea Poccetti, Matricola: 361127

class Esercente_Model_Class {
    constructor() {
        this.API_BASE_URL = "http://localhost:8001";
        this.AUTH_TOKEN = localStorage.getItem('jwt_token');
        this.CURRENT_USER = null;
    }

    init() {
        if (this.AUTH_TOKEN) {
            this.CURRENT_USER = this.parseJwt(this.AUTH_TOKEN);
            
            $.ajaxSetup({
                headers: { 
                    'Authorization': `Bearer ${this.AUTH_TOKEN}`,
                    'Accept': 'application/json'
                }
            });
        } else {
            console.warn("Nessun token trovato. Effettua il login.");
        }
    }

    parseJwt(token) {
        try {
            const base64Url = token.split('.')[1];
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            const jsonPayload = decodeURIComponent(window.atob(base64).split('').map(function(c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));
            
            const payload = JSON.parse(jsonPayload);
            
            const userData = payload.data || payload;
            
            return {
                id: userData.id || userData.id_utente || userData.id_esercente
            };
        } catch (e) {
            console.error("Errore parsing JWT", e);
            return { id: null };
        }
    }

    getShopStatus() {
        const id = this.CURRENT_USER ? this.CURRENT_USER.id : 0;
        return $.ajax({
            url: `${this.API_BASE_URL}/esercente/Get_Status.php?id_esercente=${id}`,
            method: 'GET'
        });
    }

    toggleShopStatus(nuovoStato) {
        const id = this.CURRENT_USER ? this.CURRENT_USER.id : 0;
        return $.ajax({
            url: `${this.API_BASE_URL}/esercente/Toggle_Shop_Status.php`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                id_esercente: id,
                stato_apertura: nuovoStato
            })
        });
    }

    getOrders() {
        return $.ajax({
            url: `${this.API_BASE_URL}/esercente/ordini/Get_Orders.php`,
            method: 'GET'
        });
    }

    updateOrderStatus(idOrdine, azione, codiceVerifica = null) {
        let payload = { id_ordine: idOrdine, azione: azione };
        if (codiceVerifica) payload.codice_verifica = codiceVerifica;

        return $.ajax({
            url: `${this.API_BASE_URL}/esercente/ordini/Update_Order_Status.php`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload)
        });
    }

    getMenu() {
        const id = this.CURRENT_USER ? this.CURRENT_USER.id : 0;
        return $.ajax({
            url: `${this.API_BASE_URL}/esercente/menu/Read.php?id_esercente=${id}`,
            method: 'GET'
        });
    }

    addProduct(prodottoData) {
        if(this.CURRENT_USER) prodottoData.id_esercente = this.CURRENT_USER.id;
        
        return $.ajax({
            url: `${this.API_BASE_URL}/esercente/menu/Create.php`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(prodottoData)
        });
    }

    deleteProduct(idProdotto) {
        const id = this.CURRENT_USER ? this.CURRENT_USER.id : 0;
        return $.ajax({
            url: `${this.API_BASE_URL}/esercente/menu/Delete.php`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ id_esercente: id, id_prodotto: idProdotto })
        });
    }

    toggleProductAvailability(idProdotto, isDisponibile) {
        const id = this.CURRENT_USER ? this.CURRENT_USER.id : 0;
        return $.ajax({
            url: `${this.API_BASE_URL}/esercente/menu/Toggle_Availability.php`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                id_prodotto: idProdotto,
                id_esercente: id,
                is_disponibile: isDisponibile ? 1 : 0
            })
        });
    }
}

const Esercente_Model = new Esercente_Model_Class();