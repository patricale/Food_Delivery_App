// SVOLTO DA COLUCCI PASQUALE, MATR: 358141

/**
 * HISTORY PRESENTER
 * Implementazione del pattern MVP (Model-View-Presenter).
 * * Riferimento UML: Class History_Presenter
 */


"use strict";

class History_Presenter {

    constructor() {
        this.API_URL = "http://localhost:8001/storico/get_history.php";
        
        this.tableBody = $('#orders-table-body'); 
        this.filters = $('.btn-filter');          
        
        this._initEventListeners();
        this.loadOrders('all'); 
    }

    _initEventListeners() {
        this.filters.on('click', (e) => {
            const btn = $(e.currentTarget);
            this.filters.removeClass('active btn-primary').addClass('btn-outline-primary');
            btn.removeClass('btn-outline-primary').addClass('active btn-primary');
            const filterType = btn.data('filter');
            this.loadOrders(filterType);
        });
    }

    loadOrders(filter) {
        this._showLoadingSpinner();

        const token = localStorage.getItem('jwt_token');
        const userId = localStorage.getItem('user_id');

        if (!token || !userId) {
            console.warn("Dati login mancanti. Redirect.");
            window.location.href = 'index.html';
            return;
        }

        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            if (payload.ruolo === 'esercente') {
                window.location.href = 'Esercente.html'; 
                return; 
            }
        } catch (e) {
            window.location.href = 'index.html';
            return;
        }

        $.ajax({
            url: this.API_URL,
            method: 'GET',
            data: { 
                filter: filter,
                id_cliente: userId 
            },
            dataType: 'json',
            headers: {
                "Authorization": "Bearer " + token 
            },
            
            success: (response) => {
                this._renderTable(response.records); 
            },
            error: (xhr, status, error) => {
                console.error("Errore API:", error);
                console.log("Risposta Server:", xhr.responseText); 
                
                if (xhr.status === 403 || xhr.status === 401) {
                    window.location.href = 'index.html';
                } else {
                    this._showErrorState();
                }
            }
        });
    }

    _renderTable(orders) {
        this.tableBody.empty(); 

        if (!orders || orders.length === 0) {
            // Mostra il messaggio "Nessun ordine" gestito dall'HTML
            $('#no-orders-msg').removeClass('d-none');
            this.tableBody.closest('.card').addClass('d-none'); 
            return;
        }

        // Se ci sono ordini, mostra la tabella e nascondi il messaggio vuoto
        $('#no-orders-msg').addClass('d-none');
        this.tableBody.closest('.card').removeClass('d-none');

        orders.forEach(order => {
            const dateStr = this._formatDate(order.data); 
            const badgeHtml = this._getStatusBadge(order.stato);
            const details = order.dettagli || "Nessun dettaglio";
            const restaurantName = order.ristorante || "Sconosciuto";

            const row = `
                <tr class="align-middle">
                    <td>${dateStr}</td>
                    <td>${restaurantName}</td>
                    <td>
                        <span class="d-inline-block text-truncate" style="max-width: 250px;" title="${details}">
                            ${details}
                        </span>
                            ${order.codice_ritiro ? `<br><small class="text-muted">Codice: <strong>${order.codice_ritiro}</strong></small>` : ''}
                    </td>
                    <td class="fw-bold text-nowrap">${parseFloat(order.totale).toFixed(2)} €</td>
                    <td>${badgeHtml}</td>
                    <td class="text-start" style="max-width: 150px;">
                        ${order.note ? `<small class="text-muted fst-italic">${order.note}</small>` : '<span class="text-muted small">-</span>'}
                    </td>
                </tr>
            `;
            this.tableBody.append(row);
        });
    }

    _getStatusBadge(status) {
        const s = status;
        let style = 'background-color: #6c757d; color: white;'; 

        switch (s) {
            case 'attesa':
                style = 'background-color: #ffc107; color: #000;'; 
                break;
            case 'accettato':
            case 'preparazione':
                style = 'background-color: #0d6efd; color: white;';
                break;
            case 'pronto':
            case 'ritirato':
                style = 'background-color: #198754; color: white;';
                break;
            case 'rifiutato':
            case 'nonRitirato': 
                style = 'background-color: #dc3545; color: white;';
                break;
        }

        const label = s.replace(/_/g, ' ').toUpperCase();
        return `<span class="badge rounded-pill" style="${style}">${label}</span>`;
    }

    _formatDate(isoString) {
        if (!isoString) return '-';
        const date = new Date(isoString);
        return `
            <div class="fw-bold">${date.toLocaleDateString('it-IT')}</div>
            <small class="text-muted">${date.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' })}</small>
        `;
    }

    _showLoadingSpinner() {
        this.tableBody.html(`
            <tr>
                <td colspan="5" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </td>
            </tr>
        `);
    }

    _showErrorState() {
        this.tableBody.html(`
            <tr>
                <td colspan="5" class="text-center text-danger py-3">
                    <i class="bi bi-exclamation-triangle"></i> Errore nel caricamento dati.
                </td>
            </tr>
        `);
    }
}

$(document).ready(() => {
    new History_Presenter();
});