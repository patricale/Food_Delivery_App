SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS RIGA_ORDINE, ORDINE, PRODOTTO, ESERCENTE, CLIENTE_UNIPR, UTENTE;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE UTENTE (
    id_utente INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    ruolo ENUM('studente', 'docente', 'esercente') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE CLIENTE_UNIPR (
    id_utente INT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    matricola VARCHAR(20) UNIQUE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utente) REFERENCES UTENTE(id_utente) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ESERCENTE (
    id_utente INT PRIMARY KEY,
    ragione_sociale VARCHAR(255) NOT NULL,
    p_iva VARCHAR(11) UNIQUE NOT NULL,
    indirizzo_ritiro VARCHAR(255) NOT NULL,
    stato_apertura BOOLEAN DEFAULT TRUE,
    descrizione TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utente) REFERENCES UTENTE(id_utente) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE PRODOTTO (
    id_prodotto INT PRIMARY KEY AUTO_INCREMENT,
    id_esercente INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    descrizione TEXT,
    prezzo DECIMAL(10, 2) NOT NULL,
    categoria VARCHAR(100),
    is_disponibile BOOLEAN DEFAULT TRUE,
    is_deleted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_esercente) REFERENCES ESERCENTE(id_utente) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ORDINE (
    id_ordine INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    id_esercente INT NOT NULL,
    data_ora DATETIME DEFAULT CURRENT_TIMESTAMP,
    stato ENUM('attesa', 'accettato', 'rifiutato', 'preparazione', 'pronto', 'ritirato', 'nonRitirato') DEFAULT 'attesa',
    totale DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    codice_ritiro VARCHAR(10) UNIQUE,
    note VARCHAR(250),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES CLIENTE_UNIPR(id_utente),
    FOREIGN KEY (id_esercente) REFERENCES ESERCENTE(id_utente)
) ENGINE=InnoDB;

CREATE TABLE RIGA_ORDINE (
    id_ordine INT NOT NULL,
    id_prodotto INT NOT NULL,
    quantita INT NOT NULL CHECK (quantita > 0),
    prezzo_storico DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_ordine, id_prodotto),
    FOREIGN KEY (id_ordine) REFERENCES ORDINE(id_ordine) ON DELETE CASCADE,
    FOREIGN KEY (id_prodotto) REFERENCES PRODOTTO(id_prodotto)
) ENGINE=InnoDB;

INSERT INTO UTENTE (id_utente, email, password, ruolo) VALUES 
(1, 'mario.rossi@studenti.unipr.it', '$2a$12$2KFgtCwgbrC5bWVGPMHkcO0xJywmCKtIKMNgA4Mh4bl9g6gHyrFfO', 'studente'),
(2, 'giulia.bianchi@studenti.unipr.it', '$2a$12$2KFgtCwgbrC5bWVGPMHkcO0xJywmCKtIKMNgA4Mh4bl9g6gHyrFfO', 'studente'),
(3, 'info@mensa-unipr.it', '$2a$12$2KFgtCwgbrC5bWVGPMHkcO0xJywmCKtIKMNgA4Mh4bl9g6gHyrFfO', 'esercente'),
(4, 'contatto@bar-centrale.it', '$2a$12$2KFgtCwgbrC5bWVGPMHkcO0xJywmCKtIKMNgA4Mh4bl9g6gHyrFfO', 'esercente'),
(5, 'luca.verdi@studenti.unipr.it', '$2a$12$2KFgtCwgbrC5bWVGPMHkcO0xJywmCKtIKMNgA4Mh4bl9g6gHyrFfO', 'studente'),
(6, 'sofia.neri@studenti.unipr.it', '$2a$12$2KFgtCwgbrC5bWVGPMHkcO0xJywmCKtIKMNgA4Mh4bl9g6gHyrFfO', 'studente'),
(7, 'ordini@pizzeria-uni.it', '$2a$12$2KFgtCwgbrC5bWVGPMHkcO0xJywmCKtIKMNgA4Mh4bl9g6gHyrFfO', 'esercente'),
(8, 'prof.paolo.neri@unipr.it', '$2a$12$2KFgtCwgbrC5bWVGPMHkcO0xJywmCKtIKMNgA4Mh4bl9g6gHyrFfO', 'docente');

INSERT INTO CLIENTE_UNIPR (id_utente, nome, cognome, matricola) VALUES 
(1, 'Mario', 'Rossi', '300001'),
(2, 'Giulia', 'Bianchi', '300002'),
(5, 'Luca', 'Verdi', '300003'),
(6, 'Sofia', 'Neri', '300004'),
(8, 'Paolo', 'Neri', NULL);

INSERT INTO ESERCENTE (id_utente, ragione_sociale, p_iva, indirizzo_ritiro, descrizione) VALUES 
(3, 'Mensa Parco Ducale', '01234567890', 'Viale Piacenza 1', 'Mensa universitaria principale.'),
(4, 'Bar Centrale Unipr', '09876543210', 'Via Università 12', 'Colazioni e pranzi veloci.'),
(7, 'Pizzeria La Laurea', '11223344556', 'Via D Azeglio 45', 'Pizza al taglio e da asporto.');

INSERT INTO PRODOTTO (id_prodotto, id_esercente, nome, descrizione, prezzo, categoria, is_disponibile) VALUES 
(1, 3, 'Pasta al Pomodoro', 'Pasta fresca con pomodoro e basilico', 4.50, 'Primi', 1),
(2, 3, 'Lasagne alla Bolognese', 'Classica lasagna con ragù', 6.00, 'Primi', 1),
(3, 3, 'Cotoletta alla Milanese', 'Con patatine fritte incluse', 7.50, 'Secondi', 1),
(4, 3, 'Insalata Mista', 'Lattuga, pomodori, carote e mais', 3.00, 'Contorni', 1),
(5, 3, 'Acqua Naturale 50cl', 'Bottiglietta', 1.00, 'Bevande', 1),
(6, 3, 'Coca Cola Zero', 'Lattina 33cl', 2.00, 'Bevande', 1),
(7, 3, 'Tiramisù', 'Fatto in casa', 3.50, 'Dolci', 1),
(8, 3, 'Risotto ai Funghi', 'Solo in stagione', 5.50, 'Primi', 0),
(9, 4, 'Caffè Espresso', 'Miscela Arabica 100%', 1.20, 'Caffetteria', 1),
(10, 4, 'Cappuccino', 'Latte fresco alta qualità', 1.50, 'Caffetteria', 1),
(11, 4, 'Panino Crudo', 'Focaccia farcita', 5.00, 'Panini', 1),
(12, 4, 'Trancio Margherita', 'Pizza alta al trancio', 3.50, 'Snack', 1),
(13, 7, 'Pizza Margherita', 'Classica', 5.00, 'Pizze', 1),
(14, 7, 'Pizza Diavola', 'Salame piccante', 6.50, 'Pizze', 1);

INSERT INTO ORDINE (id_ordine, id_cliente, id_esercente, data_ora, stato, totale, codice_ritiro, note) VALUES 
(101, 2, 3, NOW(), 'attesa', 10.50, 'ATT_01', 'Senza cipolla, grazie!'),
(102, 1, 3, DATE_SUB(NOW(), INTERVAL 15 MINUTE), 'preparazione', 7.50, 'PRE_02', 'Per favore, consegnare al barista se possibile.'),
(103, 6, 3, DATE_SUB(NOW(), INTERVAL 30 MINUTE), 'pronto', 7.00, 'PRT_03', 'Con tonno'),
(104, 5, 3, DATE_SUB(NOW(), INTERVAL 2 HOUR), 'ritirato', 4.50, 'OLD_04', 'Ordine di prova, da ignorare.'),
(201, 2, 4, NOW(), 'attesa', 1.20, 'BAR_01', NULL),
(105, 5, 3, DATE_ADD(NOW(), INTERVAL 1 MINUTE), 'attesa', 6.00, 'ATT_05', 'Consegna tra 1 ora, grazie.');

INSERT INTO RIGA_ORDINE (id_ordine, id_prodotto, quantita, prezzo_storico) VALUES 
(101, 1, 1, 4.50), (101, 2, 1, 6.00),
(102, 3, 1, 7.50),
(103, 7, 2, 3.50),
(104, 1, 1, 4.50),
(201, 9, 1, 1.20),
(105, 6, 3, 2.00);