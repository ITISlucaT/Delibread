-- Inserimento di nuovi prodotti per il Panificio Rossi (ID Panetteria = 1)
INSERT INTO Prodotto (Quantità, Nome, Temporaneo, Durata, GiorniDiScadenza) VALUES
(80, 'Pane ai Cereali', FALSE, 3, 2),
(45, 'Focaccia alle Olive', FALSE, 2, 1),
(25, 'Cornetti alla Crema', TRUE, 1, 1),
(35, 'Pane di Mais', FALSE, 4, 3),
(15, 'Brioche Siciliane', TRUE, 1, 1),
(50, 'Pane Pugliese Speciale', FALSE, 3, 2),
(20, 'Crostate della Casa', TRUE, 3, 2),
(40, 'Pane ai Semi di Girasole', FALSE, 4, 3);

-- Associazione dei nuovi prodotti alle tipologie appropriate
INSERT INTO Prodotto_Tipologia (IdTipologia, IdProdotto) VALUES
-- Prodotto 16: Pane ai Cereali
(1, 16), (7, 16),
-- Prodotto 17: Focaccia alle Olive
(2, 17),
-- Prodotto 18: Cornetti alla Crema
(3, 18),
-- Prodotto 19: Pane di Mais
(1, 19), (6, 19),
-- Prodotto 20: Brioche Siciliane
(3, 20),
-- Prodotto 21: Pane Pugliese Speciale
(1, 21), (6, 21),
-- Prodotto 22: Crostate della Casa
(3, 22),
-- Prodotto 23: Pane ai Semi di Girasole
(1, 23), (7, 23);

-- Aggiunta dei nuovi prodotti ai cataloghi del Panificio Rossi
INSERT INTO Catalogo_Prodotto (IdCatalogo, IdProdotto) VALUES
-- Catalogo Standard (ID 1)
(1, 16), (1, 17), (1, 19), (1, 21), (1, 23),
-- Prodotti Speciali (ID 2)
(2, 18), (2, 20), (2, 22);

-- Inserimento di nuovi ordini per il Panificio Rossi
INSERT INTO Ordine (DataConsegna, Stato, Note, IdUtente, IdPanetteria) VALUES
(CURDATE() + INTERVAL 1 DAY, 'Confermato', 'Ordine per colazione aziendale', 6, 1),
(CURDATE() + INTERVAL 2 DAY, 'In attesa', 'Prodotti integrali richiesti', 10, 1),
(CURDATE() + INTERVAL 3 DAY, 'Confermato', 'Consegna presso ufficio', 7, 1),
(CURDATE() + INTERVAL 5 DAY, 'In attesa', 'Ordine per festa di compleanno', 6, 1),
(CURDATE(), 'In preparazione', 'Ritiro entro le 18:00', 10, 1);

-- Associazione ordini alla panetteria
INSERT INTO Ordine_Panetteria (IdPanetteria, IdOrdine) VALUES
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13);

-- Inserimento prodotti negli ordini
INSERT INTO Ordine_Prodotto (IdOrdine, IdProdotto, Quantita) VALUES
-- Ordine 9: Colazione aziendale
(9, 18, 12), (9, 20, 8), (9, 1, 6), (9, 17, 3),
-- Ordine 10: Prodotti integrali
(10, 16, 4), (10, 23, 2), (10, 4, 3),
-- Ordine 11: Consegna ufficio
(11, 1, 8), (11, 17, 2), (11, 19, 3), (11, 8, 2),
-- Ordine 12: Festa compleanno
(12, 22, 2), (12, 18, 15), (12, 20, 10), (12, 6, 3),
-- Ordine 13: Ritiro serale
(13, 21, 2), (13, 16, 1), (13, 2, 1);

-- Aggiunta di alcuni ordini ricorrenti
INSERT INTO Ordine_Ricorrente (IdOrdine, Attivo) VALUES
(9, TRUE),
(11, TRUE);

-- Inserimento di notifiche relative ai nuovi ordini
INSERT INTO Notifica (Tipo, Messaggio, Letta, DataCreazione) VALUES
('Ordine', 'Nuovo ordine #9 ricevuto per colazione aziendale', FALSE, NOW()),
('Ordine', 'Ordine #12 confermato per festa di compleanno', FALSE, NOW() - INTERVAL 1 HOUR),
('Sistema', 'Prodotti stagionali aggiunti al catalogo', FALSE, NOW() - INTERVAL 3 HOUR);

-- Associazione notifiche agli utenti
INSERT INTO Utente_Notifica (IdUtente, IdNotifica) VALUES
-- Notifica al panettiere del Panificio Rossi (Giuseppe Verdi - ID 2)
(2, 6), (2, 7), (2, 8),
-- Notifica ai clienti che hanno fatto ordini
(6, 6), (6, 7),
(10, 7),
-- Notifica all'amministratore
(1, 8);


-- Query INSERT multipla per la tabella ordine

INSERT INTO `ordine` (`IdOrdine`, `DataCreazione`, `DataConsegna`, `Stato`, `Note`, `IdUtente`, `IdPanetteria`) 
VALUES 
(NULL, current_timestamp(), '2025-05-31', 'In preparazione', 'Consegna urgente', '1', '3'),
(NULL, current_timestamp(), '2025-06-01', 'In preparazione', 'Senza glutine', '1', '2'),
(NULL, current_timestamp(), '2025-06-02', 'In preparazione', NULL, '1', '1'),
(NULL, current_timestamp(), '2025-06-03', 'In preparazione', 'Consegna mattutina', '1', '4'),
(NULL, current_timestamp(), '2025-06-05', 'In preparazione', 'Festa di compleanno', '1', '6'),
(NULL, current_timestamp(), '2025-06-07', 'In preparazione', 'Chiamare prima della consegna', '1', '7'),
(NULL, current_timestamp(), '2025-06-10', 'In preparazione', NULL, '1', '8'),
(NULL, current_timestamp(), '2025-06-12', 'In preparazione', 'Ordine per ufficio', '1', '2'),
(NULL, current_timestamp(), '2025-06-15', 'In preparazione', 'Vegano', '1', '5'),
(NULL, current_timestamp(), '2025-06-18', 'In preparazione', 'Grande quantità', '1', '3');




INSERT INTO `ordine_ricorrente` (`IdOrdineRicorrente`, `IdOrdine`, `Attivo`, `Frequenza`, `GiorniSettimana`) 
VALUES 
  ('15', '11', '1', 'Giornaliera', NULL),
  ('16', '11', '1', 'Settimanale', 'Lunedì,Mercoledì,Venerdì'),
  ('17', '11', '1', 'Mensile', NULL),
  ('18', '11', '0', 'Settimanale', 'Martedì,Giovedì');