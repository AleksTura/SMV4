CREATE DATABASE SMV4; 
USE SMV4;

CREATE TABLE Ucitelj (
    Id_ucitelja int PRIMARY KEY AUTO_INCREMENT,
    Ime varchar(50),
    Priimek varchar(50),
    Geslo varchar(255)
);

CREATE TABLE Predmet (
    Id_predmeta int PRIMARY KEY AUTO_INCREMENT,
    Ime_predmeta varchar(100)
);

CREATE TABLE Ucenec (
    Id_dijaka int PRIMARY KEY AUTO_INCREMENT,
    Ime varchar(50),
    Priimek varchar(50),
    Letnik varchar(3),
    Geslo varchar(100)
);

CREATE TABLE Uci_predmet (
    Id_ucitelja int,
    Id_predmeta int,
    PRIMARY KEY (Id_ucitelja, Id_predmeta),
    FOREIGN KEY (Id_ucitelja) REFERENCES Ucitelj(Id_ucitelja),
    FOREIGN KEY (Id_predmeta) REFERENCES Predmet(Id_predmeta)
);

CREATE TABLE Vsebina (
    Id_vsebine int PRIMARY KEY,
    Id_ucitelja int,
    Id_predmeta int,
    snov varchar(50),
    FOREIGN KEY (Id_ucitelja, Id_predmeta) REFERENCES Uci_predmet(Id_ucitelja, Id_predmeta)
);

CREATE TABLE Naloga (
    Id_naloge int PRIMARY KEY,
    Id_vsebine int,
    opis_naloge varchar(50),
    komentar varchar(500),
    FOREIGN KEY (Id_vsebine) REFERENCES Vsebina(Id_vsebine)
);

CREATE TABLE Dij_predmet (
    Id_dijaka int,
    Id_ucitelja int,  
    Id_predmeta int, 
    PRIMARY KEY (Id_dijaka, Id_ucitelja, Id_predmeta),  
    FOREIGN KEY (Id_dijaka) REFERENCES Ucenec(Id_dijaka),
    FOREIGN KEY (Id_ucitelja, Id_predmeta) REFERENCES Uci_predmet(Id_ucitelja, Id_predmeta)
);
