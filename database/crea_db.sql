-- Beach Database Schema Definition (DDL)

-- 1. Drop existing tables in reverse order of dependencies to avoid FK conflicts
DROP TABLE IF EXISTS OmbrelloneVenduto;
DROP TABLE IF EXISTS GiornoDisponibilita;
DROP TABLE IF EXISTS Contratto;
DROP TABLE IF EXISTS Ombrellone;
DROP TABLE IF EXISTS TipologiaTariffa;
DROP TABLE IF EXISTS Tariffa;
DROP TABLE IF EXISTS Tipologia;
DROP TABLE IF EXISTS Cliente;

-- 2. Independent Tables

CREATE TABLE Cliente (
  codice INT PRIMARY KEY AUTO_INCREMENT,
  nome VARCHAR(100) NOT NULL,
  cognome VARCHAR(100) NOT NULL,
  dataNascita DATE,
  indirizzo VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE Tipologia (
  codice VARCHAR(50) PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  descrizione TEXT
) ENGINE=InnoDB;

CREATE TABLE Tariffa (
  codice VARCHAR(50) PRIMARY KEY,
  prezzo DECIMAL(10, 2) NOT NULL,
  dataInizio DATE NOT NULL,
  dataFine DATE NOT NULL,
  tipo VARCHAR(20) NOT NULL,
  numMinGiorni INT,
  
  -- Constraint exactly as specified in the assignment slides
  CONSTRAINT chk_tariffa_tipo CHECK (
    (tipo = 'Giornaliera' AND numMinGiorni IS NULL) 
    OR 
    (tipo = 'Abbonamento' AND numMinGiorni IS NOT NULL)
  )
) ENGINE=InnoDB;

-- 3. Dependent Tables (Level 1)

CREATE TABLE TipologiaTariffa (
  codTipologia VARCHAR(50),
  codTariffa VARCHAR(50),
  PRIMARY KEY (codTipologia, codTariffa),
  CONSTRAINT fk_tiptar_tipologia FOREIGN KEY (codTipologia) REFERENCES Tipologia(codice) ON DELETE CASCADE,
  CONSTRAINT fk_tiptar_tariffa FOREIGN KEY (codTariffa) REFERENCES Tariffa(codice) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Ombrellone (
  id INT PRIMARY KEY,
  settore INT NOT NULL,
  numFila INT NOT NULL,
  numPostoFila INT NOT NULL,
  tipologia VARCHAR(50) NOT NULL,
  CONSTRAINT fk_ombrellone_tipologia FOREIGN KEY (tipologia) REFERENCES Tipologia(codice) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE Contratto (
  numProgr INT PRIMARY KEY,
  data DATE NOT NULL,
  importo DECIMAL(10, 2) NOT NULL,
  stipulatoDa INT NOT NULL,
  CONSTRAINT fk_contratto_cliente FOREIGN KEY (stipulatoDa) REFERENCES Cliente(codice) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 4. Dependent Tables (Level 2+)

CREATE TABLE GiornoDisponibilita (
  idOmbrellone INT,
  data DATE,
  PRIMARY KEY (idOmbrellone, data),
  CONSTRAINT fk_giorno_ombrellone FOREIGN KEY (idOmbrellone) REFERENCES Ombrellone(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE OmbrelloneVenduto (
  idOmbrellone INT,
  data DATE,
  contratto INT NOT NULL,
  
  -- THE FIX: Primary key is ONLY idOmbrellone and data.
  -- This inherently prevents the same umbrella from being booked twice on the same day.
  PRIMARY KEY (idOmbrellone, data),
  
  CONSTRAINT fk_venduto_giorno FOREIGN KEY (idOmbrellone, data) REFERENCES GiornoDisponibilita(idOmbrellone, data) ON DELETE CASCADE,
  CONSTRAINT fk_venduto_contratto FOREIGN KEY (contratto) REFERENCES Contratto(numProgr) ON DELETE CASCADE
) ENGINE=InnoDB;