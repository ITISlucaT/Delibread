-- Create database (uncomment if needed)
CREATE DATABASE IF NOT EXISTS delibread;
USE delibread;


-- Create tables
CREATE TABLE Notifica (
    IdNotifica INT AUTO_INCREMENT PRIMARY KEY,
    Tipo VARCHAR(50) NOT NULL,
    Messaggio TEXT NOT NULL,
    Letta BOOLEAN DEFAULT FALSE,
    DataCreazione DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Panetteria (
    IdPanetteria INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(100) NOT NULL,
    PIva VARCHAR(20) NOT NULL,
    RagioneSociale VARCHAR(200) NOT NULL,
    Via VARCHAR(100) NOT NULL,
    Citta VARCHAR(50) NOT NULL,
    CAP VARCHAR(10) NOT NULL,
    NCiv VARCHAR(10) NOT NULL,
    Provincia VARCHAR(2) NOT NULL,
    Regione VARCHAR(50) NOT NULL,
    OrarioLimiteOrdine TIME,
    Logo BLOB
);

CREATE TABLE Rivendita (
    IdRivendita INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(100) NOT NULL,
    PIvaRagioneSociale VARCHAR(200) NOT NULL,
    Via VARCHAR(100) NOT NULL,
    Citta VARCHAR(50) NOT NULL,
    CAP VARCHAR(10) NOT NULL,
    NCiv VARCHAR(10) NOT NULL,
    Provincia VARCHAR(2) NOT NULL,
    Regione VARCHAR(50) NOT NULL
);

CREATE TABLE Utente (
    IdUtente INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(50) NOT NULL,
    Cognome VARCHAR(50) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Tipo ENUM('Amministratore', 'Panettiere', 'Rivenditore', 'Cliente') NOT NULL,
    Pwd VARCHAR(255) NOT NULL,
    Telefono VARCHAR(20),
    Attivo BOOLEAN DEFAULT TRUE,
    IdPanetteria INT,
    IdRivendita INT,
    FOREIGN KEY (IdPanetteria) REFERENCES Panetteria(IdPanetteria) ON DELETE SET NULL,
    FOREIGN KEY (IdRivendita) REFERENCES Rivendita(IdRivendita) ON DELETE SET NULL
);

CREATE TABLE Utente_Notifica (
    IdUtente INT,
    IdNotifica INT,
    PRIMARY KEY (IdUtente, IdNotifica),
    FOREIGN KEY (IdUtente) REFERENCES Utente(IdUtente) ON DELETE CASCADE,
    FOREIGN KEY (IdNotifica) REFERENCES Notifica(IdNotifica) ON DELETE CASCADE
);

CREATE TABLE Panetteria_Rivendita (
    IdPanetteria INT,
    IdRivendita INT,
    PRIMARY KEY (IdPanetteria, IdRivendita),
    FOREIGN KEY (IdPanetteria) REFERENCES Panetteria(IdPanetteria) ON DELETE CASCADE,
    FOREIGN KEY (IdRivendita) REFERENCES Rivendita(IdRivendita) ON DELETE CASCADE
);

CREATE TABLE Ordine (
    IdOrdine INT AUTO_INCREMENT PRIMARY KEY,
    DataCreazione DATETIME DEFAULT CURRENT_TIMESTAMP,
    DataConsegna DATE NOT NULL,
    Stato ENUM('In attesa', 'Confermato', 'In preparazione', 'Pronto', 'Consegnato', 'Annullato') NOT NULL DEFAULT 'In attesa',
    Note TEXT,
    IdUtente INT,
    IdPanetteria INT,
    FOREIGN KEY (IdUtente) REFERENCES Utente(IdUtente) ON DELETE SET NULL,
    FOREIGN KEY (IdPanetteria) REFERENCES Panetteria(IdPanetteria) ON DELETE SET NULL
);

CREATE TABLE Ordine_Ricorrente (
    IdOrdineRicorrente INT AUTO_INCREMENT PRIMARY KEY,
    IdOrdine INT NOT NULL,
    Attivo BOOLEAN DEFAULT TRUE,
    Frequenza ENUM('Giornaliera', 'Settimanale', 'Mensile') NOT NULL,
    GiorniSettimana VARCHAR(50), -- Giorni della settimana in cui l'ordine è attivo (es. "Lun,Mar,Mer")
    FOREIGN KEY (IdOrdine) REFERENCES Ordine(IdOrdine) ON DELETE CASCADE
);

CREATE TABLE Ordine_Panetteria (
    IdPanetteria INT,
    IdOrdine INT,
    PRIMARY KEY (IdPanetteria, IdOrdine),
    FOREIGN KEY (IdPanetteria) REFERENCES Panetteria(IdPanetteria) ON DELETE CASCADE,
    FOREIGN KEY (IdOrdine) REFERENCES Ordine(IdOrdine) ON DELETE CASCADE
);

CREATE TABLE Prodotto (
    IdProdotto INT AUTO_INCREMENT PRIMARY KEY,
    Quantità INT NOT NULL DEFAULT 0,
    DataInserimento DATETIME DEFAULT CURRENT_TIMESTAMP,
    Nome VARCHAR(100) NOT NULL,
    Temporaneo BOOLEAN DEFAULT FALSE,
    Durata INT, -- Durata in giorni
    GiorniDiScadenza INT,
    Img BLOB
);

CREATE TABLE Tipologia (
    IdTipologia INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE Prodotto_Tipologia (
    IdTipologia INT,
    IdProdotto INT,
    PRIMARY KEY (IdTipologia, IdProdotto),
    FOREIGN KEY (IdTipologia) REFERENCES Tipologia(IdTipologia) ON DELETE CASCADE,
    FOREIGN KEY (IdProdotto) REFERENCES Prodotto(IdProdotto) ON DELETE CASCADE
);

CREATE TABLE Catalogo (
    IdCatalogo INT AUTO_INCREMENT PRIMARY KEY,
    IdPanetteria INT,
    Nome VARCHAR(100) NOT NULL,
    Attivo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (IdPanetteria) REFERENCES Panetteria(IdPanetteria) ON DELETE CASCADE
);

CREATE TABLE Catalogo_Prodotto (
    IdCatalogo INT,
    IdProdotto INT,
    PRIMARY KEY (IdCatalogo, IdProdotto),
    FOREIGN KEY (IdCatalogo) REFERENCES Catalogo(IdCatalogo) ON DELETE CASCADE,
    FOREIGN KEY (IdProdotto) REFERENCES Prodotto(IdProdotto) ON DELETE CASCADE
);

CREATE TABLE Ordine_Prodotto (
    IdOrdine INT,
    IdProdotto INT,
    Quantita INT NOT NULL DEFAULT 1,
    PRIMARY KEY (IdOrdine, IdProdotto),
    FOREIGN KEY (IdOrdine) REFERENCES Ordine(IdOrdine) ON DELETE CASCADE,
    FOREIGN KEY (IdProdotto) REFERENCES Prodotto(IdProdotto) ON DELETE CASCADE
);