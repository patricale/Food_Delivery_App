// SVOLTO DA Alessandro Di Stasi 358140
// FILE: Cliente_Presenter.js

$(document).ready(function() {
    let currentRestaurantId = localStorage.getItem('fdc_restaurant_id') || null;
    let cart = JSON.parse(localStorage.getItem('fdc_cart')) || [];

    const token = localStorage.getItem('jwt_token');
    const ID_CLIENTE = localStorage.getItem('user_id');

    if (!token || !ID_CLIENTE) {
        window.location.href = "index.html"; 
        return;
    }

    
    $.ajaxSetup({
        headers: { 'Authorization': 'Bearer ' + token }
    });

    // URL DEL BACKEND (Porta 8001)
    const API_URL = "http://localhost:8001"; 

    loadRestaurants();
    updateCartUI();

    function loadRestaurants() {
        $("#restaurants-container").html('<div class="spinner-border text-warning" role="status"></div>');
        
        
        $.getJSON(API_URL + '/restaurants/Get_All_Restaurants.php') 
            .done(function(data) {
                $("#restaurants-container").empty();
                if(data.records && data.records.length > 0) {
                    data.records.forEach(r => {
                        $("#restaurants-container").append(`
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm border-0">
                                <img src="${r.immagine_url}" class="card-img-top" style="height: 160px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold">${r.nome}</h5>
                                    <p class="text-muted small">${r.descrizione}</p>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="badge ${r.aperto ? 'bg-success' : 'bg-danger'}">${r.aperto ? 'APERTO' : 'CHIUSO'}</span>
                                        ${r.aperto ? `<button class="btn btn-sm btn-outline-warning text-dark btn-view-menu" data-id="${r.id}" data-name="${r.nome}">Vedi Menù</button>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>`);
                    });
                } else {
                    $("#restaurants-container").html('<p class="text-muted">Nessun ristorante disponibile.</p>');
                }
            });
    }

    $(document).on('click', '.btn-view-menu', function() {
        let newRestId = $(this).data('id');
        let newRestName = $(this).data('name');

       
        if (cart.length > 0 && currentRestaurantId != null && currentRestaurantId != newRestId) {
            if (!confirm("Hai prodotti di un altro ristorante nel carrello. Vuoi svuotarlo?")) return;
            cart = [];
            currentRestaurantId = null;
            updateCartUI();
        }

        currentRestaurantId = newRestId;
       
        localStorage.setItem('fdc_restaurant_id', currentRestaurantId);

        $("#menu-title").text("Menù: " + newRestName);
        loadMenu(newRestId);
        $('html, body').animate({scrollTop: $("#menu-section").offset().top - 100}, 500);
    });

    function loadMenu(restaurantId) {
        $("#menu-container").html('<div class="spinner-border text-warning"></div>');
        $("#menu-section").removeClass("d-none");

        
        $.getJSON(API_URL + '/restaurants/Get_Menu.php?id=' + restaurantId)
            .done(function(data) {
                $("#menu-container").empty();
                data.records.forEach(p => {
                    $("#menu-container").append(`
                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 border-bottom py-3">
                        <div>
                            <h6 class="mb-1 fw-bold">${p.nome}</h6>
                            <small class="text-muted">${p.descrizione}</small>
                            <div class="fw-bold text-primary">€${parseFloat(p.prezzo).toFixed(2)}</div>
                        </div>
                        <button class="btn btn-sm btn-primary rounded-circle btn-add-cart" data-id="${p.id}" data-name="${p.nome}" data-price="${p.prezzo}">+</button>
                    </div>`);
                });
            });
    }

    $(document).on('click', '.btn-add-cart', function() {
        let product = { id: $(this).data('id'), name: $(this).data('name'), price: parseFloat($(this).data('price')), qty: 1 };
        let existing = cart.find(x => x.id === product.id);
        existing ? existing.qty++ : cart.push(product);
        updateCartUI();
    });

    function updateCartUI() {
        localStorage.setItem('fdc_cart', JSON.stringify(cart));
        
       
        if (cart.length === 0) {
            localStorage.removeItem('fdc_restaurant_id');
            currentRestaurantId = null;
        }
        
        $("#cart-items").empty();
        let total = 0;
        cart.forEach((item, index) => {
            total += item.price * item.qty;
            $("#cart-items").append(`
            <div class="d-flex justify-content-between align-items-center mb-2 small">
                <span>${item.qty}x ${item.name}</span>
                <div>
                    <span class="fw-bold me-2">€${(item.price * item.qty).toFixed(2)}</span>
                    <button class="btn btn-xs btn-outline-danger btn-remove" data-index="${index}">×</button>
                </div>
            </div>`);
        });
        $("#cart-total").text(`€${total.toFixed(2)}`);
        $("#btn-order").prop("disabled", cart.length === 0).text(cart.length === 0 ? "Carrello Vuoto" : "Ordina Ora");
    }

    $(document).on('click', '.btn-remove', function() {
        cart.splice($(this).data('index'), 1);
        if(cart.length === 0) currentRestaurantId = null;
        updateCartUI();
    });

    $("#btn-order").click(async function() {
    if (cart.length === 0) {
        alert("Il carrello è vuoto!");
        return;
    }

    const token = localStorage.getItem('jwt_token');
    const ID_CLIENTE = localStorage.getItem('user_id');

    const $btn = $(this);
    $btn.prop("disabled", true).text("Invio in corso...");
    
    let noteText = $("#order-notes").val().trim();

    
    const totaleCalcolato = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    
    console.log("Invio ordine per cliente:", ID_CLIENTE, "Totale:", totaleCalcolato);

    const payload = {
        id_cliente: ID_CLIENTE,
        id_esercente: currentRestaurantId,
        totale: totaleCalcolato,
        note: noteText,
        items: cart.map(item => ({
            id: item.id,
            qty: item.qty
        }))
    };

    try {
        const response = await fetch(API_URL + '/orders/Create_Order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (response.ok) {
            alert("✅ Ordine creato con successo!");
            cart = [];
            localStorage.removeItem('fdc_cart');
            localStorage.removeItem('fdc_restaurant_id');
            window.location.href = 'Storico.html';
        } else {
            alert("❌ Errore: " + result.message);
        }
    } catch (error) {
        console.error("Errore:", error);
        alert("❌ Errore di connessione al server.");
    } finally {
        $btn.prop("disabled", false).text("Ordina Ora");
    }
    });
});