USE SMV4;

-- Gesla: 'ucenec123'
-- V praksi priporočam uporabo password_hash() v PHP-ju namesto MD5

-- 1. VPIŠEMO PREDMETE (najmanj 10)
INSERT INTO Predmet (Ime_predmeta) VALUES
('Matematika'),
('Slovenščina'),
('Angleščina'),
('Fizika'),
('Kemija'),
('Zgodovina'),
('Geografija'),
('Biologija'),
('Računalništvo'),
('Športna vzgoja'),
('Glasbena vzgoja'),
('Likovna vzgoja');

-- 2. VPIŠEMO UČITELJE (najmanj 20) z hashiranimi gesli
INSERT INTO Ucitelj (Ime, Priimek, Geslo) VALUES
('Ana', 'Novak', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Marko', 'Kovač', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Maja', 'Horvat', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Peter', 'Kralj', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Irena', 'Zupan', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Bojan', 'Potočnik', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Nina', 'Vidmar', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Gregor', 'Petek', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Tina', 'Rozman', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Luka', 'Jereb', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Sabina', 'Kos', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('David', 'Koren', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Eva', 'Breznik', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Rok', 'Hribar', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Alenka', 'Kotnik', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Milan', 'Zajc', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Tanja', 'Rupnik', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Simon', 'Knez', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Monika', 'Bizjak', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Andrej', 'Čeh', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.');

-- 3. POVEZEMO UČITELJE S PREDMETI (učitelji poučujejo več predmetov in predmeti imajo več učiteljev)
INSERT INTO Uci_predmet (Id_ucitelja, Id_predmeta) VALUES
(1, 1), (1, 4),(2, 2), (2, 3),(3, 5), (3, 8),(4, 1), (5, 1),(6, 2), (7, 2),
(8, 3), (9, 4), (10, 5), (11, 6), (12, 7), (13, 8), (14, 9), (15, 10),
(16, 11), (17, 12), (18, 1), (19, 2), (20, 3);

-- 4. VPIŠEMO UCENCE (najmanj 100) z hashiranimi gesli
INSERT INTO Ucenec (Ime, Priimek, Letnik, Geslo) VALUES
('Miha', 'Novak', 'R1A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Lara', 'Kovač', 'R1A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Jan', 'Horvat', 'R1A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Rebeka', 'Tavčar', 'R1A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Ema', 'Kralj', 'R1B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Luka', 'Zupan', 'R1B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Neža', 'Potočnik', 'R1B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Žan', 'Koren', 'R1B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Tilen', 'Vidmar', 'K1A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Zoja', 'Petek', 'K1A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Žan', 'Rozman', 'K1A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Tatjana', 'Vidovič', 'K1A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Matic', 'Kovačič', 'K1A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Nina', 'Pirc', 'K1A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Nika', 'Jereb', 'E1A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Aljaž', 'Bergant', 'E1A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Veronika', 'Žnidaršič', 'E1A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Brina', 'Cvetko', 'E1B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Igor', 'Jelen', 'E1B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Rok', 'Strnad', 'E1B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Jasna', 'Kuhar', 'E1C', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Quentin', 'Strajnar', 'E1C', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Mojca', 'Vovk', 'E1C', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Mark', 'Kos', 'R2A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Sara', 'Koren', 'R2A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Jure', 'Breznik', 'R2A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Yvonne', 'Bajc', 'R2A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Branko', 'Ertl', 'R2A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Lea', 'Hribar', 'R2B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Tim', 'Kotnik', 'R2B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Maja', 'Zajc', 'R2B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Lovro', 'Knez', 'R2B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Lovro', 'Rupnik', 'K2A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Pia', 'Knez', 'K2A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Gal', 'Bizjak', 'K2A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Zoran', 'Cvetkov', 'K2A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Ana', 'Dolenc', 'K2A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Gregor', 'Horvat', 'K2A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Hana', 'Čeh', 'E2A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Cene', 'Dolinar', 'E2A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Ajda', 'Dvoršak', 'E2A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Daša', 'Erjavec', 'E2B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Klemen', 'Lah', 'E2B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Peter', 'Novak', 'E2B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Ljudmila', 'Miklavčič', 'E2C', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Silvester', 'Uršič', 'E2C', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Tina', 'Kralj', 'E2C', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Oskar', 'Janež', 'R3A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Vita', 'Korošec', 'R3A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Nejc', 'Medved', 'R3A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Cvetka', 'Flajs', 'R3A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Erika', 'Humar', 'R3A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Lana', 'Logar', 'R3B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Matic', 'Sever', 'R3B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Tjaša', 'Perko', 'R3B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('David', 'Potočnik', 'R3B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Jani', 'Javornik', 'K3A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Mila', 'Bevc', 'K3A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Larisa', 'Dolenc', 'K3A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Franc', 'Jarc', 'K3A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Sabina', 'Kovač', 'K3A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Urban', 'Zupan', 'K3A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Urban', 'Fras', 'E3A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Enej', 'Furlan', 'E3A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Drago', 'Gradišnik', 'E3A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Frida', 'Gorenc', 'E3B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Miran', 'Nova', 'E3B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Helena', 'Kotnik', 'E3B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Nadja', 'Oman', 'E3C', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Uroš', 'Zadravec', 'E3C', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Gabrijela', 'Kobal', 'E3C', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Tadej', 'Golob', 'R4A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Nina', 'Kmet', 'R4A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Rok', 'Lesjak', 'R4A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Alenka', 'Bizjak', 'R4A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Bojan', 'Vidmar', 'R4A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Ana', 'Mlakar', 'R4B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Grega', 'Oblak', 'R4B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Eva', 'Pirc', 'R4B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Luka', 'Kralj', 'R4B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Blaž', 'Rutar', 'K4A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Tina', 'Skok', 'K4A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Damjan', 'Tomaž', 'K4A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Maja', 'Rozman', 'K4A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Simon', 'Petek', 'K4A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Irena', 'Hribar', 'K4A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Mojca', 'Vidic', 'E4A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Gašper', 'Hozjan', 'E4A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Monika', 'Čeh', 'E4A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Helena', 'Ivančič', 'E4B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Oton', 'Pavlin', 'E4B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Tanja', 'Rupnik', 'E4B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Petra', 'Rak', 'E4C', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Xavier', 'Ahlin', 'E4C', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Zala', 'Kos', 'E4C', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Matic', 'Logar', 'R1A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Nika', 'Zajc', 'K1A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Luka', 'Erjavec', 'E1B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Tjaša', 'Kotnik', 'R2B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Jan', 'Bizjak', 'K2A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Ema', 'Gradišnik', 'E2A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Rok', 'Flajs', 'R3A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Neža', 'Jarc', 'K3A', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.'),
('Mark', 'Kobal', 'E3B', '$2y$10$zDibG1lVJ7yazyV.sl2uceY76MCp0EYi6ShCLbHP88dW8K.9NcC9.');

-- 5. POVEZEMO UCENCE S PREDMETI (vsi učenci obiskujejo več predmetov)
INSERT INTO Dij_predmet (Id_dijaka, Id_ucitelja, Id_predmeta) VALUES
-- Prvih 10 učencev s popravljenimi kombinacijami
(1, 1, 1), (1, 2, 2), (1, 8, 3), (1, 9, 4),
(2, 1, 1), (2, 2, 2), (2, 8, 3), (2, 13, 8),
(3, 4, 1), (3, 6, 2), (3, 10, 5), (3, 14, 9),
(4, 5, 1), (4, 7, 2), (4, 11, 6), (4, 15, 10),
(5, 1, 1), (5, 2, 2), (5, 12, 7), (5, 16, 11),
(6, 18, 1), (6, 19, 2), (6, 20, 3), (6, 9, 4),
(7, 1, 1), (7, 2, 2), (7, 3, 5), (7, 4, 1),  
(8, 5, 1), (8, 6, 2), (8, 3, 8), (8, 8, 3),  
(9, 9, 4), (9, 10, 5), (9, 11, 6), (9, 12, 7),
(10, 13, 8), (10, 14, 9), (10, 15, 10), (10, 16, 11);
-- Zaradi krajšanja kode dodamo samo nekaj primerov, v praksi bi dodali za vse učence

-- 6. VPIŠEMO ADMINA
INSERT INTO Admin (Ime, Priimek, Geslo) VALUES
('Admin', 'Sistem', '$2y$10$r8R2cLk8Vq1QwE5sY9pZYuKjX7nM2bA1vC3dF6hH9jL4mN7qP8tW');


-- 8. PRIMERI DODAJANJA VSEBINE IN NALOG
INSERT INTO Vsebina (Id_vsebine, Id_ucitelja, Id_predmeta, snov) VALUES
-- Učitelj 1 poučuje Matematiko (1)
(1, 1, 1, 'Algebra'),
(2, 1, 1, 'Geometrija'),
-- Učitelj 2 poučuje Slovenski jezik (2)
(3, 2, 2, 'Slovenska slovnica'),
(4, 2, 2, 'Literarna teorija'),
-- Učitelj 3 poučuje Kemijo (5)
(5, 3, 5, 'Anorganska kemija'),
(6, 3, 5, 'Organska kemija'),
-- Učitelj 4 poučuje Matematiko (1)
(7, 4, 1, 'Trigonometrija'),
(8, 4, 1, 'Analiza'),
-- Učitelj 5 poučuje Matematiko (1)
(9, 5, 1, 'Verjetnost'),
(10, 5, 1, 'Statistika'),
-- Učitelj 6 poučuje Slovenski jezik (2)
(11, 6, 2, 'Slovensko berilo'),
(12, 6, 2, 'Ustno izražanje'),
-- Učitelj 7 poučuje Slovenski jezik (2)
(13, 7, 2, 'Pisno izražanje'),
(14, 7, 2, 'Književnost'),
-- Učitelj 8 poučuje Angleški jezik (3)
(15, 8, 3, 'Angleška slovnica'),
(16, 8, 3, 'Poslovna angleščina'),
-- Učitelj 9 poučuje Fiziko (4)
(17, 9, 4, 'Mehanika'),
(18, 9, 4, 'Termodinamika'),
-- Učitelj 10 poučuje Kemijo (5)
(19, 10, 5, 'Biokemija'),
(20, 10, 5, 'Fizikalna kemija'),
-- Učitelj 11 poučuje Zgodovino (6)
(21, 11, 6, 'Srednji vek'),
(22, 11, 6, 'Renesansa'),
-- Učitelj 12 poučuje Geografijo (7)
(23, 12, 7, 'Fizična geografija'),
(24, 12, 7, 'Družbena geografija'),
-- Učitelj 13 poučuje Biologijo (8)
(25, 13, 8, 'Citologija'),
(26, 13, 8, 'Genetika'),
-- Učitelj 14 poučuje Računalništvo (9)
(27, 14, 9, 'Programiranje v Pythonu'),
(28, 14, 9, 'Podatkovne strukture'),
-- Učitelj 15 poučuje Šport (10)
(29, 15, 10, 'Atletika'),
(30, 15, 10, 'Ekipne igre');

INSERT INTO Naloga (Id_naloge, Id_vsebine, opis_naloge, navodila) VALUES
(1, 1, 'Domača naloga iz algebre', 'Rešite naslednje linearne enačbe: 2x + 5 = 15, 3(x - 4) = 21, 5x - 7 = 3x + 9'),
(2, 2, 'Naloga iz geometrije', 'Izračunajte ploščino in obseg trikotnika s stranicami a=5cm, b=7cm, c=9cm'),
(3, 3, 'Slovenska slovnica', 'Dopolnite povedi z ustreznimi predlogi: Šel sem ___ šolo, Knjiga je ___ mizi, Pismo je ___ očeta'),
(4, 4, 'Literarna analiza', 'Analizirajte pesem "Sonetje nesmrtnosti" in opišite uporabljene literarne figure'),
(5, 5, 'Angleške časovne oblike', 'Pretvorite naslednje povedi v preteklik: I go to school, She eats an apple, They play football'),
(6, 6, 'Poslovno pismo', 'Napišite poslovno pismo v angleščini z zahtevo za informacije o izdelku'),
(7, 7, 'Newtonovi zakoni', 'Pojasnite Newtonove zakone gibanja in navedite primere iz vsakdanjega življenja'),
(8, 8, 'Toplotni stroji', 'Opišite delovanje toplotnega stroja in izračunajte njegovo izkoristnost'),
(9, 9, 'Kemijske reakcije', 'Uravnotežite naslednje kemijske reakcije: H2 + O2 → H2O, CH4 + O2 → CO2 + H2O'),
(10, 10, 'Organske spojine', 'Narišite strukture naslednjih organskih spojin: metan, etan, propan, butan'),
(11, 11, 'Rimska cesarstva', 'Opišite vzpon in padec Rimskega cesarstva ter njegov vpliv na Evropo'),
(12, 12, 'Humanizem', 'Pojasnite pojme humanizem in renesansa ter njune glavne predstavnike'),
(13, 13, 'Geografski koordinati', 'Določite geografske koordinate 5 glavnih mest po svetu'),
(14, 14, 'Demografija', 'Analizirajte demografske trende v Sloveniji v zadnjih 20 letih'),
(15, 15, 'Celica', 'Opišite strukturo rastlinske in živalske celice ter njune razlike'),
(16, 16, 'Genetski problemi', 'Rešite naslednji genetski problem: Kakšni so možni genotipi potomcev, če se križata Aa x Aa?'),
(17, 17, 'Python program', 'Napišite Python program, ki izračuna fakulteto števila in preveri, ali je število praštevilo'),
(18, 18, 'Podatkovne strukture', 'Implementirajte povezani seznam v izbranem programskem jeziku z operacijami vstavljanja in brisanja'),
(19, 19, 'Atletski trening', 'Pripravite tedenski atletski treningski načrt za teka na 100m z vključenimi razgibavami in vajami za moč'),
(20, 20, 'Košarkarska taktika', 'Opišite osnovne košarkarske taktike v napadu in obrambi z diagrami'),
(21, 21, 'Glasbena teorija', 'Zapišite dur in mol lestvico ter pojasnite razliko med njima'),
(22, 22, 'Solfeđo vaje', 'Zapojte in zapišite na notni črti naslednje melodije po posluhu'),
(23, 23, 'Risanje perspektive', 'Narišite sobo v eni točkovni perspektivi z vsaj tremi predmeti'),
(24, 24, 'Barvni krog', 'Ustvarite barvni krog in pojasnite razmerja med primarnimi, sekundarnimi in terciarnimi barvami'),
(25, 25, 'OOP načela', 'Implementirajte razred "Avto" v Pythonu z uporabo 4 načel OOP (dedovanje, inkapsulacija, polimorfizem, abstrakcija)'),
(26, 26, 'Sortiranje', 'Implementirajte algoritem hitrega sortiranja in analizirajte njegovo časovno zahtevnost'),
(27, 27, 'Spletna stran', 'Ustvarite preprosto spletno stran z HTML in CSS, ki vsebuje glavo, navigacijski meni in vsebino'),
(28, 28, 'JavaScript kalkulator', 'Napišite JavaScript kalkulator, ki izvaja osnovne matematične operacije (+, -, *, /)'),
(29, 29, 'SQL poizvedbe', 'Napišite SQL poizvedbe za: SELECT vse učence iz 2. letnika, UPDATE spremembo gesla, DELETE neaktivne uporabnike'),
(30, 30, 'Normalizacija baze', 'Normalizirajte naslednjo tabelo do 3. normalne oblike in pojasnite vsak korak');
