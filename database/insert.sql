-- Inserimento dati nella tabella Panetteria
INSERT INTO Panetteria (Nome, PIva, RagioneSociale, Via, Citta, CAP, NCiv, Provincia, Regione, OrarioLimiteOrdine) VALUES
('Panificio Rossi', '12345678901', 'Panificio Rossi s.r.l.', 'Via Roma', 'Milano', '20100', '15', 'MI', 'Lombardia', '16:00:00'),
('Pane e Delizie', '23456789012', 'Pane e Delizie di Bianchi', 'Via Garibaldi', 'Roma', '00100', '25', 'RM', 'Lazio', '15:30:00'),
('Forno Antico', '34567890123', 'Forno Antico s.n.c.', 'Via Verdi', 'Napoli', '80100', '8', 'NA', 'Campania', '17:00:00'),
('Pane Quotidiano', '45678901234', 'Pane Quotidiano di Verdi', 'Corso Italia', 'Torino', '10100', '45', 'TO', 'Piemonte', '18:00:00'),
('Il Fornaio', '56789012345', 'Il Fornaio s.a.s.', 'Via Dante', 'Firenze', '50100', '12', 'FI', 'Toscana', '16:30:00');

-- Inserimento dati nella tabella Rivendita
INSERT INTO Rivendita (Nome, PIvaRagioneSociale, Via, Citta, CAP, NCiv, Provincia, Regione) VALUES
('Alimentari Verdi', 'Alimentari Verdi di Paolo Verdi - P.IVA 67890123456', 'Via Mazzini', 'Milano', '20100', '30', 'MI', 'Lombardia'),
('Market Express', 'Market Express s.r.l. - P.IVA 78901234567', 'Via Cavour', 'Roma', '00100', '10', 'RM', 'Lazio'),
('Bottega del Pane', 'Bottega del Pane di Mario Rossi - P.IVA 89012345678', 'Via Napoli', 'Firenze', '50100', '5', 'FI', 'Toscana'),
('SuperStore', 'SuperStore s.p.a. - P.IVA 90123456789', 'Corso Vittorio', 'Torino', '10100', '75', 'TO', 'Piemonte'),
('Mini Market', 'Mini Market di Luigi Bianchi - P.IVA 01234567890', 'Via Bologna', 'Napoli', '80100', '15', 'NA', 'Campania');

-- Inserimento dati nella tabella Panetteria_Rivendita
INSERT INTO Panetteria_Rivendita (IdPanetteria, IdRivendita) VALUES
(1, 1), (1, 2), (1, 3),
(2, 2), (2, 4),
(3, 3), (3, 5),
(4, 1), (4, 4),
(5, 2), (5, 5);

-- Inserimento dati nella tabella Utente
INSERT INTO Utente (Nome, Cognome, Email, Tipo, Pwd, Telefono, Attivo, IdPanetteria, IdRivendita) VALUES
('Mario', 'Rossi', 'mario.rossi@email.com', 'Amministratore', SHA2('password123', 256), '3331234567', TRUE, NULL, NULL),
('Giuseppe', 'Verdi', 'giuseppe.verdi@email.com', 'Panettiere', SHA2('panefresco', 256), '3389876543', TRUE, 1, NULL),
('Anna', 'Bianchi', 'anna.bianchi@email.com', 'Panettiere', SHA2('farina00', 256), '3371122334', TRUE, 2, NULL),
('Franco', 'Neri', 'franco.neri@email.com', 'Rivenditore', SHA2('market2023', 256), '3356677889', TRUE, NULL, 1),
('Laura', 'Gialli', 'laura.gialli@email.com', 'Rivenditore', SHA2('bottega456', 256), '3398765432', TRUE, NULL, 3),
('Paolo', 'Blu', 'paolo.blu@email.com', 'Cliente', SHA2('clienteabc', 256), '3312345678', TRUE, NULL, NULL),
('Giulia', 'Viola', 'giulia.viola@email.com', 'Cliente', SHA2('acquisti789', 256), '3387654321', TRUE, NULL, NULL),
('Marco', 'Arancio', 'marco.arancio@email.com', 'Panettiere', SHA2('fornello', 256), '3351234567', TRUE, 3, NULL),
('Lucia', 'Marrone', 'lucia.marrone@email.com', 'Rivenditore', SHA2('negozio123', 256), '3399876543', TRUE, NULL, 2),
('Roberto', 'Celeste', 'roberto.celeste@email.com', 'Cliente', SHA2('cliente456', 256), '3352233445', TRUE, NULL, NULL);

-- Inserimento dati nella tabella Notifica
INSERT INTO Notifica (Tipo, Messaggio, Letta, DataCreazione) VALUES
('Ordine', 'Nuovo ordine ricevuto #12345', FALSE, NOW() - INTERVAL 2 DAY),
('Sistema', 'Manutenzione programmata per domani', TRUE, NOW() - INTERVAL 5 DAY),
('Promozione', 'Sconto del 10% su tutti i prodotti', FALSE, NOW() - INTERVAL 1 DAY),
('Ordine', 'Lordine #12346 è stato consegnato', TRUE, NOW() - INTERVAL 3 DAY),
('Sistema', 'Aggiornamento completato con successo', FALSE, NOW());

-- Inserimento dati nella tabella Utente_Notifica
INSERT INTO Utente_Notifica (IdUtente, IdNotifica) VALUES
(1, 1), (1, 2), (1, 5),
(2, 1), (2, 3),
(3, 4),
(6, 3), (6, 4),
(7, 3);

-- Inserimento dati nella tabella Tipologia
INSERT INTO Tipologia (Nome) VALUES
('Pane'),
('Focaccia'),
('Dolci'),
('Pizze'),
('Grissini'),
('Pane speciale'),
('Prodotti integrali'),
('Prodotti senza glutine');

-- Inserimento dati nella tabella Prodotto
INSERT INTO Prodotto (Quantità, Nome, Temporaneo, Durata, GiorniDiScadenza) VALUES
(100, 'Pane Comune', FALSE, 2, 1),
(50, 'Focaccia Genovese', FALSE, 1, 1),
(30, 'Baguette', FALSE, 1, 1),
(20, 'Pane Integrale', FALSE, 3, 2),
(40, 'Pane di Segale', FALSE, 4, 3),
(25, 'Pizza Margherita', TRUE, 1, 1),
(15, 'Croissant', TRUE, 1, 1),
(60, 'Grissini Torinesi', FALSE, 15, 10),
(35, 'Ciabatta', FALSE, 2, 1),
(10, 'Panettone Artigianale', TRUE, 21, 15),
(25, 'Pane Pugliese', FALSE, 3, 2),
(30, 'Pane Toscano', FALSE, 2, 1),
(15, 'Pane di Kamut', FALSE, 3, 2),
(20, 'Pane senza Glutine', FALSE, 5, 3),
(10, 'Dolce al Cioccolato', TRUE, 4, 2);

-- Inserimento dati nella tabella Prodotto_Tipologia
INSERT INTO Prodotto_Tipologia (IdTipologia, IdProdotto) VALUES
(1, 1), (1, 3), (1, 4), (1, 5), (1, 9), (1, 11), (1, 12),
(2, 2),
(3, 7), (3, 10), (3, 15),
(4, 6),
(5, 8),
(6, 13),
(7, 4), (7, 13),
(8, 14);

-- Inserimento dati nella tabella Catalogo
INSERT INTO Catalogo (IdPanetteria, Nome, Attivo) VALUES
(1, 'Catalogo Standard', TRUE),
(1, 'Prodotti Speciali', TRUE),
(2, 'Catalogo Principale', TRUE),
(3, 'Tradizionale', TRUE),
(4, 'Prodotti Base', TRUE),
(4, 'Specialità Regionali', TRUE),
(5, 'Catalogo Completo', TRUE);

-- Inserimento dati nella tabella Catalogo_Prodotto
INSERT INTO Catalogo_Prodotto (IdCatalogo, IdProdotto) VALUES
(1, 1), (1, 2), (1, 3), (1, 8), (1, 9),
(2, 4), (2, 5), (2, 13), (2, 14),
(3, 1), (3, 2), (3, 4), (3, 6), (3, 7),
(4, 3), (4, 9), (4, 11), (4, 12),
(5, 1), (5, 3), (5, 8),
(6, 5), (6, 10), (6, 11), (6, 12),
(7, 1), (7, 2), (7, 3), (7, 4), (7, 5), (7, 6), (7, 7), (7, 8), (7, 9), (7, 15);

-- Inserimento dati nella tabella Ordine
INSERT INTO Ordine (DataConsegna, Stato, Note, IdUtente, IdPanetteria) VALUES
(CURDATE() + INTERVAL 1 DAY, 'Confermato', 'Consegna al mattino', 6, 1),
(CURDATE() + INTERVAL 2 DAY, 'In attesa', 'Ordine speciale', 7, 2),
(CURDATE() + INTERVAL 1 DAY, 'In preparazione', NULL, 6, 3),
(CURDATE() + INTERVAL 3 DAY, 'Confermato', 'Consegna pomeridiana', 10, 4),
(CURDATE(), 'Pronto', 'Ritiro in negozio', 7, 5),
(CURDATE() - INTERVAL 1 DAY, 'Consegnato', NULL, 6, 1),
(CURDATE() - INTERVAL 2 DAY, 'Consegnato', 'Cliente soddisfatto', 7, 2),
(CURDATE() + INTERVAL 4 DAY, 'In attesa', 'Ordine per evento', 10, 3);

-- Inserimento dati nella tabella Ordine_Ricorrente
INSERT INTO Ordine_Ricorrente (IdOrdine, Attivo) VALUES
(1, TRUE),
(3, TRUE),
(4, FALSE);

-- Inserimento dati nella tabella Ordine_Panetteria
INSERT INTO Ordine_Panetteria (IdPanetteria, IdOrdine) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(1, 6),
(2, 7),
(3, 8);

-- Inserimento dati nella tabella Ordine_Prodotto
INSERT INTO Ordine_Prodotto (IdOrdine, IdProdotto, Quantita) VALUES
(1, 1, 5), (1, 2, 2), (1, 8, 1),
(2, 4, 3), (2, 7, 6),
(3, 11, 2), (3, 12, 2),
(4, 1, 10), (4, 3, 5), (4, 9, 3),
(5, 2, 4), (5, 6, 2), (5, 15, 1),
(6, 1, 3), (6, 3, 2),
(7, 4, 2), (7, 5, 1), (7, 13, 1),
(8, 1, 15), (8, 2, 10), (8, 6, 5), (8, 7, 20);


INSERT INTO Ordine_Ricorrente (IdOrdine, Frequenza, GiorniSettimana, Attivo) VALUES
(1, 'Settimanale', 'Lun, Mar', 1),
(2, 'Settimanale', 'Mer, Ven', 1),
(3, 'Mensile', '1 del mese', 1),
(4, 'Mensile', '15 del mese', 0),
(5, 'Settimanale', 'Gio, Sab', 1),
(6, 'Mensile', 'Ultimo giorno del mese', 1),
(7, 'Giornaliera', 'Tutti i giorni', 1),
(8, 'Settimanale', 'Dom', 0),
(9, 'Giornaliera', 'Tutti i giorni', 0),
(10, 'Mensile', '10 del mese', 1);
