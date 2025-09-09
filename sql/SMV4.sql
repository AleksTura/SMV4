-- CREATE DATABASE SMV4 
GO 

CREATE TABLE U�itelj (
    Id_u�itelja INT PRIMARY KEY,
    Ime VARCHAR(50),
    Priimek VARCHAR(50),
    Geslo VARCHAR(255)
);

CREATE TABLE Predmet (
    Id_predmeta INT PRIMARY KEY,
    Ime_predmeta VARCHAR(100)
);

CREATE TABLE U�enec (
    Id_dijaka INT PRIMARY KEY,
    Ime VARCHAR(50),
    Priimek VARCHAR(50),
    Letnik INT,
    O�_�ola VARCHAR(100)
);

CREATE TABLE U�i_predmet (
    Id_u�itelja INT,
    Id_predmeta INT,
    PRIMARY KEY (Id_u�itelja, Id_predmeta),
    FOREIGN KEY (Id_u�itelja) REFERENCES U�itelj(Id_u�itelja),
    FOREIGN KEY (Id_predmeta) REFERENCES Predmet(Id_predmeta)
);

CREATE TABLE Dij_predmet (
    Id_dijaka INT,
    Id_predmeta INT,
    PRIMARY KEY (Id_dijaka, Id_predmeta),
    FOREIGN KEY (Id_dijaka) REFERENCES U�enec(Id_dijaka),
    FOREIGN KEY (Id_predmeta) REFERENCES Predmet(Id_predmeta)
); -->

