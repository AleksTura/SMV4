CREATE DATABASE SMV4 
GO 

CREATE TABLE Uèitelj (
    Id_uèitelja INT PRIMARY KEY,
    Ime VARCHAR(50),
    Priimek VARCHAR(50),
    Geslo VARCHAR(255)
);

CREATE TABLE Predmet (
    Id_predmeta INT PRIMARY KEY,
    Ime_predmeta VARCHAR(100)
);

CREATE TABLE Uèenec (
    Id_dijaka INT PRIMARY KEY,
    Ime VARCHAR(50),
    Priimek VARCHAR(50),
    Letnik INT,
    Oš_šola VARCHAR(100)
);

CREATE TABLE Uèi_predmet (
    Id_uèitelja INT,
    Id_predmeta INT,
    PRIMARY KEY (Id_uèitelja, Id_predmeta),
    FOREIGN KEY (Id_uèitelja) REFERENCES Uèitelj(Id_uèitelja),
    FOREIGN KEY (Id_predmeta) REFERENCES Predmet(Id_predmeta)
);

CREATE TABLE Dij_predmet (
    Id_dijaka INT,
    Id_predmeta INT,
    PRIMARY KEY (Id_dijaka, Id_predmeta),
    FOREIGN KEY (Id_dijaka) REFERENCES Uèenec(Id_dijaka),
    FOREIGN KEY (Id_predmeta) REFERENCES Predmet(Id_predmeta)
);

