// Autore: Andrea Poccetti, Matricola: 361127

class Esercente_Presenter_Class {
    constructor() {
        this.isShopOpen = false;
        this.currentSection = 'ordini';
        this.refreshInterval = null;
    }

    init() {
        Esercente_Model.init();
        Esercente_View.bindEvents(this);
        this.loadStatus();
        window.addEventListener('pageshow', (event) => {
            console.log("Ritorno sulla Dashboard: aggiorno i dati...");
            this.loadStatus(); 
        });
        this.render_section('ordini');
    }

    render_section(section) {
        this.currentSection = section;
        if (this.refreshInterval) clearInterval(this.refreshInterval);
        
        Esercente_View.renderSection(section);
        Esercente_View.updateHeaderStatus(this.isShopOpen);
        
        if (section === 'ordini') {
            this.loadOrders();
            this.refreshInterval = setInterval(() => this.loadOrders(), 2000); 
        } else if (section === 'menu') {
            this.loadMenu();
        }
    }

    onNavigation(section) {
        this.render_section(section);
    }

    onShopToggle(isChecked) {
        Esercente_Model.toggleShopStatus(isChecked)
            .done(() => {
                this.isShopOpen = isChecked;
                Esercente_View.updateHeaderStatus(isChecked);
                if (this.currentSection === 'menu') this.loadMenu();
            })
            .fail(() => {
                alert("Errore comunicazione server");
                Esercente_View.updateHeaderStatus(!isChecked);
            });
    }

    onOrderUpdate(idOrdine, azione) {
        Esercente_Model.updateOrderStatus(idOrdine, azione)
            .done((res) => {
                if(res.success) this.loadOrders();
                else alert(res.message);
            })
            .fail((err) => alert("Errore API: " + err.status));
    }

    onVerifyCode(idOrdine, codice) {
        if (!codice) { Esercente_View.showErrorModal(); return; }
        
        Esercente_Model.updateOrderStatus(idOrdine, 'ritirato', codice)
            .done((res) => {
                if (res.success) {
                    Esercente_View.showSuccessModal();
                    this.loadOrders();
                } else {
                    Esercente_View.showErrorModal();
                }
            });
    }

    onAddProduct(data) {
        if (!data.nome || !data.prezzo) return alert("Dati mancanti");
        
        Esercente_Model.addProduct(data)
            .done(() => {
                Esercente_View.hideAddModal();
                this.loadMenu();
            })
            .fail((xhr) => alert(xhr.responseJSON?.message || "Errore aggiunta"));
    }

    onDeleteProduct(id) {
        Esercente_Model.deleteProduct(id)
            .done(() => this.loadMenu())
            .fail(() => alert("Errore eliminazione"));
    }

    onToggleProduct(id, isChecked) {
        Esercente_Model.toggleProductAvailability(id, isChecked)
            .fail(() => this.loadMenu());
    }

    loadStatus() {
        Esercente_Model.getShopStatus().done(res => {
            this.isShopOpen = res.stato_apertura;
            Esercente_View.updateHeaderStatus(this.isShopOpen);
            $('#shop-name-display').text(res.ragione_sociale);
        });
    }

    loadOrders() {
        Esercente_Model.getOrders().done(ordini => {
            Esercente_View.renderOrders(ordini);
        });
    }

    loadMenu() {
        Esercente_Model.getMenu().done(prodotti => {
            Esercente_View.renderMenu(prodotti, this.isShopOpen);
        });
    }
}

const Esercente_Presenter = new Esercente_Presenter_Class();

$(document).ready(() => {
    Esercente_Presenter.init();
});