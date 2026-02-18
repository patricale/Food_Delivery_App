// Autore: Andrea Poccetti, Matricola: 361127

class Esercente_View_Class {
    constructor() {
        this.templates = {
            ordini: `
                <h2 class="fw-bold text-dark mb-4" style="font-size: 1.75rem;">Dashboard Ordini</h2>
                <div class="row h-100 g-4">
                    <div class="col-md-4"><div class="kanban-column"><div class="kanban-header-card header-orange rounded-4"><div class="d-flex align-items-center gap-2"><i data-lucide="clock" style="color: #d95c00; width: 24px; height: 24px;"></i><span>Ordini in arrivo</span></div><span class="badge rounded-pill" id="count-attesa">0</span></div><div class="kanban-body-list" id="col-attesa"></div></div></div>
                    <div class="col-md-4"><div class="kanban-column"><div class="kanban-header-card header-blue rounded-4"><div class="d-flex align-items-center gap-2"><i data-lucide="package" style="color: #0044cc; width: 24px; height: 24px;"></i><span>In preparazione</span></div><span class="badge rounded-pill" id="count-preparazione">0</span></div><div class="kanban-body-list" id="col-preparazione"></div></div></div>
                    <div class="col-md-4"><div class="kanban-column"><div class="kanban-header-card header-green rounded-4"><div class="d-flex align-items-center gap-2"><i data-lucide="check-circle" style="color: #1e7e34; width: 24px; height: 24px;"></i><span>Pronto per il ritiro</span></div><span class="badge rounded-pill" id="count-pronto">0</span></div><div class="kanban-body-list" id="col-pronto"></div></div></div>
                </div>
                <div class="modal fade" id="modalErroreRitiro" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow-lg rounded-4"><div class="modal-body p-4 text-center"><h4 class="fw-bold text-dark mb-2">Codice Errato</h4><button type="button" class="btn btn-rifiuta rounded-pill px-4 fw-bold w-100" data-bs-dismiss="modal">Riprova</button></div></div></div></div>
            `,
            menu: `
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div><h2 class="fw-bold mb-1 text-dark">Il Mio Menu</h2><p class="text-muted small mb-0">Gestisci i piatti</p></div>
                    <button id="btn-nuovo-piatto" class="btn btn-fdc rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAggiungiPiatto">+ NUOVO PIATTO</button>
                </div>
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="fdc-table-header-gray text-secondary small text-uppercase">
                                <tr><th class="ps-4 border-0">Prodotto</th><th class="border-0">Categoria</th><th class="border-0">Prezzo</th><th class="border-0">Stato</th><th class="text-end pe-4 border-0">Azioni</th></tr>
                            </thead>
                            <tbody id="menu-table-body" class="border-top-0"></tbody>
                        </table>
                    </div>
                </div>
                <div id="menu-loading-msg" class="text-center py-3 text-muted small">Aggiornamento catalogo...</div>
            `,
        };
    }

    renderSection(sectionId) {
        const template = this.templates[sectionId];
        if (template) {
            $('#dashboard-content').hide().html(template).fadeIn(200);
            if (window.lucide) lucide.createIcons();
        }
    }

    updateHeaderStatus(isAperto) {
        const toggle = $('#header-toggle-apertura');
        const label = $('#header-status-label');
        toggle.prop('checked', isAperto);
        
        if (isAperto) {
            label.text("APERTO").removeClass('text-danger').addClass('text-success');
            $('#btn-nuovo-piatto').prop('disabled', true).attr('title', 'Chiudi il locale per aggiungere piatti');
            $('.btn-delete-product').prop('disabled', true);
        } else {
            label.text("CHIUSO").removeClass('text-success').addClass('text-danger');
            $('#btn-nuovo-piatto').prop('disabled', false).removeAttr('title');
            $('.btn-delete-product').prop('disabled', false);
        }
    }

    renderOrders(ordini) {
        let counts = { attesa: 0, preparazione: 0, pronto: 0 };
        const validOrderIds = new Set(); 

        ordini.forEach(ordine => {
            let targetId = '', footer = '', borderClass = '';
            
            if (ordine.stato === 'attesa') {
                counts.attesa++;
                targetId = 'col-attesa';
                borderClass = 'border-attesa';
                footer = `
                    <div class="d-flex gap-2">
                        <button class="btn-custom-sm btn-accetta flex-grow-1 d-flex gap-2 justify-content-center" data-action="accetta" data-id="${ordine.id_ordine}"><i data-lucide="check" style="width:18px;"></i> Accetta</button>
                        <button class="btn-custom-sm btn-rifiuta flex-grow-1 d-flex gap-2 justify-content-center" data-action="rifiuta" data-id="${ordine.id_ordine}"><i data-lucide="x-circle" style="width:18px;"></i> Rifiuta</button>
                    </div>`;
            } else if (ordine.stato === 'accettato' || ordine.stato === 'preparazione') {
                counts.preparazione++;
                targetId = 'col-preparazione';
                borderClass = 'border-prep';
                footer = `<div class="d-grid"><button class="btn-custom-sm btn-pronto d-flex gap-2 justify-content-center" data-action="pronto" data-id="${ordine.id_ordine}"><i data-lucide="check-circle" style="width:18px;"></i> Pronto per il ritiro</button></div>`;
            } else if (ordine.stato === 'pronto') {
                counts.pronto++;
                targetId = 'col-pronto';
                borderClass = 'border-pronto';
                footer = `
                    <nav class="order-footer-actions">
                        <div class="d-flex gap-2">
                            <input type="text" id="otp-${ordine.id_ordine}" class="form-control input-otp-custom rounded-3" placeholder="Codice" autocomplete="off">
                            <button class="btn-custom-sm btn-accetta px-3 d-flex gap-2 justify-content-center" data-action="verifica" data-id="${ordine.id_ordine}">Verifica</button>
                        </div>
                        <div class="text-center"><button type="button" class="btn-non-ritirato" data-action="non_ritirato_modal" data-id="${ordine.id_ordine}">Segna come non ritirato</button></div>
                    </nav>`;
            }

            if (!targetId) return;
            validOrderIds.add(ordine.id_ordine);

            const cardId = `order-card-${ordine.id_ordine}`;
            const existingCard = $(`#${cardId}`);
            const inputId = `otp-${ordine.id_ordine}`;

            if (existingCard.length > 0 && $(`#${inputId}`).is(':focus')) {
                return; 
            }

            let articoliHtml = ordine.articoli.map(a => 
                `<div class="d-flex justify-content-between mb-1"><span class="text-dark small"><strong>${a.quantita}x</strong> ${a.nome}</span></div>`
            ).join('');

            const innerHtml = `
                <div class="p-3">
                    <div class="mb-2"><div class="fw-bold text-dark">#ORD-${ordine.id_ordine}</div><div class="text-muted small">${ordine.data_ora}</div></div>
                    <div class="mb-3">${articoliHtml}</div>
                    
                    ${ordine.note ? `
                        <div style="background-color: #fff9e6; border-left: 4px solid #fcae4e; padding: 8px; border-radius: 6px; font-size: 0.85rem; color: #856404; margin-bottom: 15px;">
                            <strong>Note:</strong> ${ordine.note}
                        </div>` : ''}

                    <hr class="hr-thick">
                    <div class="fw-bold fs-5 text-dark mb-3">€ ${parseFloat(ordine.totale).toFixed(2)}</div>
                    ${footer}
                </div>`;

            if (existingCard.length > 0) {
                existingCard.html(innerHtml);
                existingCard.attr('class', `order-card fade-in mb-3 bg-white shadow-sm rounded-4 border ${borderClass}`);
                if (existingCard.parent().attr('id') !== targetId) {
                    existingCard.detach().appendTo(`#${targetId}`);
                }
            } else {
                const newCard = `<div id="${cardId}" class="order-card fade-in mb-3 bg-white shadow-sm rounded-4 border ${borderClass}" data-id="${ordine.id_ordine}">${innerHtml}</div>`;
                $(`#${targetId}`).append(newCard);
            }
        });

        $('.order-card').each(function() {
            const id = parseInt($(this).attr('data-id'));
            if (!validOrderIds.has(id)) {
                $(this).fadeOut(200, function() { $(this).remove(); });
            }
        });

        $('#count-attesa').text(counts.attesa);
        $('#count-preparazione').text(counts.preparazione);
        $('#count-pronto').text(counts.pronto);
        
        if (window.lucide) lucide.createIcons();
    }

    renderMenu(prodotti, isShopOpen) {
        const tbody = $('#menu-table-body').empty();
        $('#menu-loading-msg').hide();

        if (prodotti.length === 0) {
            tbody.html('<tr><td colspan="5" class="text-center py-5">Nessun prodotto.</td></tr>');
            return;
        }

        prodotti.forEach(p => {
            const isChecked = p.is_disponibile == 1 ? 'checked' : '';
            const row = `
                <tr>
                    <td class="ps-4"><span class="fw-bold text-dark">${p.nome}</span><br><small class="text-muted">${p.descrizione || ''}</small></td>
                    <td><span class="badge bg-light text-dark border">${p.categoria}</span></td>
                    <td class="fw-bold">€ ${parseFloat(p.prezzo).toFixed(2)}</td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input toggle-prod-avail" type="checkbox" data-id="${p.id_prodotto}" ${isChecked}>
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-danger btn-delete-product rounded-pill px-3" data-id="${p.id_prodotto}" ${isShopOpen ? 'disabled title="Chiudi il locale per eliminare"' : ''}>Elimina</button>
                    </td>
                </tr>`;
            tbody.append(row);
        });
    }

    showSuccessModal() { $('#modalSuccessoRitiro').modal('show'); setTimeout(() => $('#modalSuccessoRitiro').modal('hide'), 2500); }
    showErrorModal() { $('#modalErroreRitiro').modal('show'); }
    showConfirmModal() { $('#modalConfermaNonRitirato').modal('show'); }
    hideConfirmModal() { $('#modalConfermaNonRitirato').modal('hide'); }
    hideAddModal() { $('#modalAggiungiPiatto').modal('hide'); $('#formAggiungiPiatto')[0].reset(); }
    showDeleteModal() { $('#modalConfermaEliminazione').modal('show'); }
    hideDeleteModal() { $('#modalConfermaEliminazione').modal('hide'); }
    
    bindEvents(presenter) {
        $('.nav-link-custom').on('click', function(e) {
            e.preventDefault();
            $('.nav-link-custom').removeClass('active');
            $(this).addClass('active');
            presenter.onNavigation($(this).data('section'));
        });

        $(document).on('change', '#header-toggle-apertura', function() {
            presenter.onShopToggle($(this).is(':checked'));
        });

        $(document).on('click', '[data-action="accetta"]', function() { presenter.onOrderUpdate($(this).data('id'), 'accetta'); });
        $(document).on('click', '[data-action="rifiuta"]', function() { presenter.onOrderUpdate($(this).data('id'), 'rifiuta'); });
        $(document).on('click', '[data-action="pronto"]', function() { presenter.onOrderUpdate($(this).data('id'), 'pronto'); });
        
        $(document).on('click', '[data-action="verifica"]', function() { 
            const id = $(this).data('id');
            const code = $(`#otp-${id}`).val();
            presenter.onVerifyCode(id, code); 
        });
        
        $(document).on('keypress', '.input-otp-custom', function(e) {
            if(e.which === 13) {
                const id = $(this).attr('id').split('-')[1];
                const code = $(this).val();
                presenter.onVerifyCode(id, code);
            }
        });
        
        $(document).on('click', '[data-action="non_ritirato_modal"]', function() { 
            $('#btn-conferma-non-ritirato-submit').data('id-ordine', $(this).data('id'));
            Esercente_View.showConfirmModal();
        });
        
        $('#btn-conferma-non-ritirato-submit').on('click', function() {
            presenter.onOrderUpdate($(this).data('id-ordine'), 'nonRitirato');
            Esercente_View.hideConfirmModal();
        });

        $('#formAggiungiPiatto').on('submit', function(e) {
            e.preventDefault();
            presenter.onAddProduct({
                nome: $('#input-nome').val(),
                categoria: $('#input-categoria').val(),
                prezzo: $('#input-prezzo').val(),
                descrizione: $('#input-descrizione').val()
            });
        });
        
        $(document).on('click', '.btn-delete-product', function() { 
            const id = $(this).data('id');
            $('#btn-conferma-eliminazione-submit').data('id-prodotto', id); 
            Esercente_View.showDeleteModal();
        });

        $('#btn-conferma-eliminazione-submit').on('click', function() {
            const id = $(this).data('id-prodotto');
            presenter.onDeleteProduct(id);
            Esercente_View.hideDeleteModal();
        });
        
        $(document).on('change', '.toggle-prod-avail', function() { presenter.onToggleProduct($(this).data('id'), $(this).is(':checked')); });
        $('#profile-btn').on('click', function(e) {
            e.preventDefault();
            window.location.href = 'Profilo.html';
        });
    }

    setRagioneSociale(nome) {
        $('#shop-name-display').text(nome);
    }
}

const Esercente_View = new Esercente_View_Class();