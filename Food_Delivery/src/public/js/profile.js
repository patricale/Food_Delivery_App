// SVOLTO DA COLUCCI PASQUALE, MATR. 358141

class ProfileManager_JS {
    constructor() {
        this.apiBaseUrl = 'http://localhost:8001';
        this.jwtToken = localStorage.getItem('jwt_token');
        
        this.dom = {
            form: $('#profileForm'),
            feedbackBox: $('#feedbackBox'),
            pwd: $('#password'),
            pwdConfirm: $('#confirmPassword')
        };
    }

    init() {
        this.jwtToken = localStorage.getItem('jwt_token');

        // Security Check
        if (!this.jwtToken) {
            window.location.href = 'index.html';
            return;
        }

        try {
            const parts = this.jwtToken.split('.');
            if (parts.length === 3) {
                const payload = JSON.parse(atob(parts[1]));
                console.log("Debug Token Payload:", payload); 

                // Prendo il ruolo, gestisco il caso in cui manchi, e lo rendo minuscolo
                const userRole = (payload.ruolo || payload.role || '').toLowerCase();

                if (userRole === 'esercente') {
                    console.log("Rilevato Esercente -> Configuro Navbar.");
                    
                    // 1. Nascondo Storico Ordini (inutile qui per esercente)
                    $('#storico').hide();

                    // 2. Nascondo il pulsante "Profilo"
                    $('#nav-profilo').hide();

                    // 3. Trasformo il tasto "Ristoranti" in "Dashboard" 
                    $('#ristoranti').show(); 
                    $('#link-home').attr('href', 'Esercente.html');
                    $('#link-home').html('<i class="bi bi-speedometer2"></i> Dashboard');
                }
            }
        } catch (e) {
            console.warn("Impossibile leggere il ruolo dal token (verrà riprovato dopo il caricamento dati):", e);
        }

        // Caricamento dati
        this.loadProfileData();

        // Gestione Tasto Annulla
        $('#btn-annulla').off('click').on('click', () => {
            if (confirm("Vuoi davvero annullare le modifiche non salvate?")) {
                this.loadProfileData(); // Ricarica i dati originali dal DB
                this.showFeedback("Modifiche annullate. Dati ripristinati.", 'success');
            }
        });

        // Gestione Logout
        $('#logout-btn').off('click').on('click', (e) => {
            e.preventDefault();
            localStorage.removeItem('jwt_token');
            window.location.href = 'index.html';
        });

        // Gestione Salvataggio
        this.dom.form.off('submit').on('submit', (e) => {
            this.handleUpdateSubmit(e);
        });
    }

    loadProfileData() {
        const context = this;
        $.ajax({
            url: `${this.apiBaseUrl}/profilo/get_profile.php`,
            method: 'GET',
            headers: { "Authorization": "Bearer " + this.jwtToken },
            dataType: 'json',
            success: function(response) {
                context.currentRole = (response.ruolo || '').toLowerCase(); 
                if (context.currentRole === 'esercente') {
                    $('#storico').hide();
                    $('#nav-profilo').hide();
                    $('#link-home').attr('href', 'Esercente.html');
                    $('#link-home').html('<i class="bi bi-speedometer2"></i> Dashboard');
                } else {
                    $('#link-home').attr('href', 'Cliente.html');
                    $('#link-home').html('<i class="bi bi-shop"></i>Home');
                }

                $('#email').val(response.email);
                
                context.toggleMerchantFields(context.currentRole);

                if (context.currentRole === 'studente' || context.currentRole === 'docente') {
                    $('#nome').val(response.nome);
                    $('#cognome').val(response.cognome);
                    $('#matricola').val(response.matricola).prop('readonly', true);
                } else {
                    $('#ragione_sociale').val(response.ragione_sociale);
                    $('#p_iva').val(response.p_iva);
                    $('#indirizzo_ritiro').val(response.indirizzo_ritiro);
                    $('#descrizione').val(response.descrizione);
                }
            },
            error: (xhr) => {
                if (xhr.status === 401) {
                    localStorage.removeItem('jwt_token');
                    window.location.href = 'index.html';
                } else {
                    this.showFeedback("Errore caricamento dati.", 'danger');
                }
            }
        });
    }

    toggleMerchantFields(role) {
        if (role === 'studente' || role === 'docente') {
            $('#studentFields').removeClass('d-none');
            $('#merchantFields').addClass('d-none');
        } else {
            $('#merchantFields').removeClass('d-none');
            $('#studentFields').addClass('d-none');
        }
    }

    handleUpdateSubmit(e) {
        e.preventDefault();
        
        const p1 = this.dom.pwd.val();
        const p2 = this.dom.pwdConfirm.val();

        if (p1.length > 0 && p1 !== p2) {
            this.showFeedback('Le password non coincidono.', 'warning');
            return;
        }

        let payload = { 
            password: p1 
        };
        
        if (this.currentRole === 'studente' || this.currentRole === 'docente') {
            payload.nome = $('#nome').val();
            payload.cognome = $('#cognome').val();
        } else {
            payload.ragione_sociale = $('#ragione_sociale').val();
            payload.p_iva = $('#p_iva').val();
            payload.indirizzo_ritiro = $('#indirizzo_ritiro').val();
            payload.descrizione = $('#descrizione').val();
        }

        const context = this;
        
        $.ajax({
            url: `${this.apiBaseUrl}/profilo/update_profile.php`,
            method: 'POST',
            contentType: 'application/json',
            headers: { "Authorization": "Bearer " + this.jwtToken },
            data: JSON.stringify(payload),
            success: function(response) {
                context.showFeedback('Profilo aggiornato con successo!', 'success');
                context.dom.pwd.val('');
                context.dom.pwdConfirm.val('');
            },
            error: function(xhr) {
                let msg = 'Errore server';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                    if (xhr.responseJSON.errors) {
                        msg += ': ' + xhr.responseJSON.errors.join(', ');
                    }
                }
                context.showFeedback(msg, 'danger');
            }
        });
    }

    showFeedback(message, type) {
        this.dom.feedbackBox
            .removeClass('d-none alert-success alert-danger alert-warning')
            .addClass(`alert-${type}`)
            .text(message);
        window.scrollTo(0, 0);
    }
}

$(document).ready(() => { new ProfileManager_JS().init(); });