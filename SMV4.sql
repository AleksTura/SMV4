CREATE DATABASE SMV4 
GO 

CREATE TABLE Ucitelj (
    Id_ucitelja INT PRIMARY KEY,
    Ime VARCHAR(50),
    Priimek VARCHAR(50),
    Geslo VARCHAR(255)
);

CREATE TABLE Predmet (
    Id_predmeta INT PRIMARY KEY,
    Ime_predmeta VARCHAR(100)
);

CREATE TABLE Ucenec (
    Id_dijaka INT PRIMARY KEY,
    Ime VARCHAR(50),
    Priimek VARCHAR(50),
    Letnik INT,
    Geslo VARCHAR(100)
);

CREATE TABLE Uci_predmet (
    Id_ucitelja INT,
    Id_predmeta INT,
    PRIMARY KEY (Id_ucitelja, Id_predmeta),
    FOREIGN KEY (Id_ucitelja) REFERENCES Ucitelj(Id_ucitelja),
    FOREIGN KEY (Id_predmeta) REFERENCES Predmet(Id_predmeta)
);

CREATE TABLE Dij_predmet (
    Id_dijaka INT,
    Id_predmeta INT,
    PRIMARY KEY (Id_dijaka, Id_predmeta),
    FOREIGN KEY (Id_dijaka) REFERENCES Ucenec(Id_dijaka),
    FOREIGN KEY (Id_predmeta) REFERENCES Predmet(Id_predmeta)
); 

