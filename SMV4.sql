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
    Letnik int,
    Geslo varchar(100)
);

CREATE TABLE Uci_predmet (
    Id_ucitelja int,
    Id_predmeta int,
    PRIMARY KEY (Id_ucitelja, Id_predmeta),
    FOREIGN KEY (Id_ucitelja) REFERENCES Ucitelj(Id_ucitelja),
    FOREIGN KEY (Id_predmeta) REFERENCES Predmet(Id_predmeta),
    snov varchar(50)
);

CREATE TABLE Dij_predmet (
    Id_dijaka int,
    Id_ucitelja int,  
    Id_predmeta int, 
    PRIMARY KEY (Id_dijaka, Id_ucitelja, Id_predmeta),  
    FOREIGN KEY (Id_dijaka) REFERENCES Ucenec(Id_dijaka),
    FOREIGN KEY (Id_ucitelja, Id_predmeta) REFERENCES Uci_predmet(Id_ucitelja, Id_predmeta),  
    naloge varchar(50),
    komentarji varchar(500)
);
