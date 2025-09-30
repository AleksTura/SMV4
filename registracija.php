<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Zberemo podatke iz obrazca
    $ime = trim($_POST['ime'] ?? '');
    $priimek = trim($_POST['priimek'] ?? '');
    $naziv = $_POST['naziv'] ?? '';
    $letnik = trim($_POST['letnik'] ?? '');
    $geslo1 = $_POST['geslo1'] ?? '';
    $geslo2 = $_POST['geslo2'] ?? '';

    // Priprava seznama napak
    $errors = [];

    // Preverimo, če so vsi podatki pravilno izpolnjeni
    if (empty($ime)) {
        $errors[] = "Ime je obvezno.";
    }
    if (empty($priimek)) {
        $errors[] = "Priimek je obvezen.";
    }

    if ($naziv === 'Dijak' && empty($letnik)) {
        $errors[] = "Letnik je obvezen za dijake.";
    }
    if (empty($geslo1) || empty($geslo2)) {
        $errors[] = "Geslo in potrditveno geslo morata biti izpolnjena.";
    } elseif ($geslo1 !== $geslo2) {
        $errors[] = "Gesli se ne ujemata.";
    }

    // Če so napake, jih izpišemo
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
        echo "<p><a href='javascript:history.back()'>Nazaj na obrazec</a></p>";
        exit; // Preprečimo nadaljevanje izvedbe kode
    }
/*
    // Shranjevanje gesla in drugih podatkov v bazo
    $hashedPassword = password_hash($geslo1, PASSWORD_DEFAULT);

    // TO DO: Shrani podatke v bazo tukaj (MySQL)

    // Povezava z bazo
    $servername = "localhost"; // Tvoj DB gostitelj
    $username = "root";        // Tvoje uporabniško ime
    $password = "";            // Tvoje geslo
    $dbname = "my_database";   // Tvoje ime baze

    // Ustvarimo povezavo z bazo
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Preverimo povezavo
    if ($conn->connect_error) {
        die("Povezava z bazo ni uspela: " . $conn->connect_error);
    }

    // Dodajanje podatkov v bazo (učitelj/dijak)
        if ($naziv === "Učitelj") {
            $sql = "INSERT INTO Ucitelj (ime, priimek, geslo) VALUES ('$ime', '$priimek', '$hashedPassword')";
            $conn->query($sql);

            // Po vnosu, pridobimo ID učitelja
            $sql = "SELECT Id_ucitelja FROM Ucitelj WHERE ime = '$ime' AND geslo = '$hashedPassword' LIMIT 1"; 
            $result = $conn->query($sql);
            if ($result && $row = $result->fetch_assoc()) {
                $_SESSION["UserId"] = $row['Id_ucitelja'];  // Shranimo ID v sejo
            }
        } 
        else if ($naziv === "Dijak") {
            $sql = "INSERT INTO Ucenec (ime, priimek, letnik, geslo) VALUES ('$ime', '$priimek', '$letnik', '$hashedPassword')";
            $conn->query($sql);

            // Po vnosu, pridobimo ID dijaka
            $sql = "SELECT Id_dijaka FROM Ucenec WHERE ime = '$ime' AND geslo = '$hashedPassword' LIMIT 1"; 
            $result = $conn->query($sql);
            if ($result && $row = $result->fetch_assoc()) {
                $_SESSION["UserId"] = $row['Id_dijaka'];  // Shranimo ID v sejo
            }
        }

    */


    // Po uspešni registraciji preusmeri uporabnika na dobrodošlico ali drugo stran
    header('Location: prijava.php');
    exit; // Preprečimo nadaljevanje izvajanja preostale kode

} 
?>



<html>
    <head>
        <title>Registracija</title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="izgled.css">
        <meta name="author" content="Špela Zeme">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div class="VpisBox">
            <h1>Registracija</h1>
            <form action="registracija.php" method="POST">
                <label for="ime">Ime</label><br>
                <input type="text" name="ime" id="ime" placeholder="Janez"><br>
                <label for="priimek">Priimek</label><br>
                <input type="text" name="priimek" id="priimek" placeholder="Novak"><br>
                <label for="naziv">Izberi naziv</label><br>
                <input type="radio" name="naziv" id="učitelj" value="učitelj">
                <label for="učitelj">Učitelj</label>
                <input type="radio" name="naziv" id="dijak" value="dijak" checked>
                <label for="dijak">Dijak</label><br>
                <div id="letnik-container">
                    <label for="letnik">Letnik</label><br>
                    <input type="text" name="letnik" id="letnik" placeholder="R3B"><br>
                </div>
                <label for="geslo1">Geslo</label><br>
                <input type="password" name="geslo1" id="geslo1" placeholder="************"><br>
                <label for="geslo2">Potrdi geslo</label><br>
                <input type="password" name="geslo2" id="geslo2" placeholder="************"><br>
                <input type="submit" class="PrijavaButton" value="Registriraj se">
            </form>
        </div>
    </body>
</html>


<script>
    document.getElementById('dijak').addEventListener('change', toggleLetnik);
    document.getElementById('učitelj').addEventListener('change', toggleLetnik);

    // Pokliči funkcijo ob nalaganju strani, da nastavi pravilno stanje
    window.onload = toggleLetnik;

    // Funkcija, ki pokaže/skrije letnik glede na izbran radio gumb
    function toggleLetnik() {
        const dijakRadio = document.getElementById('dijak');
        const letnikContainer = document.getElementById('letnik-container');

        if (dijakRadio.checked) {
            letnikContainer.style.display = 'block';
        } else {
            letnikContainer.style.display = 'none';
        }
    }
</script>

