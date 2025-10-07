<?php
session_start();

    // Povezava z bazo
    $servername = "localhost"; // Tvoj DB gostitelj
    $username = "root";        // Tvoje uporabniško ime
    $password = "";            // Tvoje geslo
    $dbname = "smv4";   // Tvoje ime baze

    // Ustvarimo povezavo z bazo
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Preverimo povezavo
    if ($conn->connect_error) {
        die("Povezava z bazo ni uspela: " . $conn->connect_error);
    }

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

    if ($naziv === 'dijak' && empty($letnik)) {
        $errors[] = "Letnik je obvezen za dijake.";
    }
    if (empty($geslo1) || empty($geslo2)) {
        $errors[] = "Geslo in potrditveno geslo morata biti izpolnjena.";
    } elseif ($geslo1 !== $geslo2) {
        $errors[] = "Gesli se ne ujemata.";
    }


    // Shranjevanje gesla in drugih podatkov v bazo
    $hashedPassword = password_hash($geslo1, PASSWORD_DEFAULT);

    // 1. Najprej poišči uporabnika po imenu/priimku
    $sql = "SELECT Id_dijaka, geslo FROM Ucenec WHERE ime = ? AND priimek = ? LIMIT 1"; 
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $ime, $priimek);
    $stmt->execute();
    $result = $stmt->get_result();

    // 2. Če uporabnik obstaja, preveri geslo
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $storedHash = $row['geslo'];
        
        // 3. Preveri, če se geslo ujema z obstoječim hashom
        if (password_verify($geslo1, $storedHash)) {
            $errors[] = "Uporabnik s tem imenom, priimkom in geslom že obstaja";
        } else {
            $errors[] = "Uporabnik s tem imenom in priimkom že obstaja, vendar z drugim geslom";
        }
    }
    // Če so napake, jih izpišemo

    // V delu, kjer obdelujete napake, namesto shranjevanja v sejo:
    if (!empty($errors)) {
        echo "<script>alert('" . addslashes(implode(" ", $errors)) . "'); window.history.back();</script>";
        exit;
    }






    // Dodajanje podatkov v bazo (učitelj/dijak)
    if ($naziv === "učitelj") {
        $sql = "INSERT INTO Ucitelj (ime, priimek, geslo) VALUES ('$ime', '$priimek', '$hashedPassword')";
        $conn->query($sql);

        // Po vnosu, pridobimo ID učitelja
        $sql = "SELECT Id_ucitelja FROM Ucitelj WHERE ime = '$ime' AND geslo = '$hashedPassword' LIMIT 1"; 
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            $_SESSION["UserId"] = $row['Id_ucitelja'];  // Shranimo ID v sejo
        }
    } 
    else if ($naziv === "dijak") {
        $sql = "INSERT INTO Ucenec (ime, priimek, letnik, geslo) VALUES ('$ime', '$priimek', '$letnik', '$hashedPassword')";
        $conn->query($sql);

        // Po vnosu, pridobimo ID dijaka
        $sql = "SELECT Id_dijaka FROM Ucenec WHERE ime = '$ime' AND geslo = '$hashedPassword' LIMIT 1"; 
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            $_SESSION["UserId"] = $row['Id_dijaka'];  // Shranimo ID v sejo
        }
    }

    $uporabniškoIme = "$ime$priimek";

    // Po uspešni registraciji preusmeri uporabnika na dobrodošlico ali drugo stran
    // Po uspešni registraciji pokažemo alert in preusmerimo na prijavo
    echo "<script>
    alert('Registracija uspešna za uporabnika: " . addslashes($uporabniškoIme) . "');
    window.location.href = 'prijava.php';
    </script>";
    exit; // Preprečimo nadaljevanje izvajanja preostale kode

} 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registracija</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="izgled.css">
    <meta name="author" content="Špela Zeme">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="floating-elements" id="floatingElements"></div>
    
    <div class="glass-card">
        <div class="header-card">
            <h1>Registracija</h1>
        </div>
        
        <div class="VpisBox">
            <form action="registracija.php" method="POST">
                <div class="form-group">
                    <label for="ime">Ime</label>
                    <input type="text" name="ime" id="ime" class="form-control" placeholder="Janez" required>
                </div>
                
                <div class="form-group">
                    <label for="priimek">Priimek</label>
                    <input type="text" name="priimek" id="priimek" class="form-control" placeholder="Novak" required>
                </div>
                
                <div class="form-group">
                    <label>Izberi naziv</label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="naziv" id="učitelj" value="učitelj">
                            <label for="učitelj">Učitelj</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="naziv" id="dijak" value="dijak" checked>
                            <label for="dijak">Dijak</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group" id="letnik-container">
                    <label for="letnik">Letnik</label>
                    <input type="text" name="letnik" id="letnik" class="form-control" placeholder="R3B">
                </div>
                
                <div class="form-group">
                    <label for="geslo1">Geslo</label>
                    <input type="password" name="geslo1" id="geslo1" class="form-control" placeholder="************" required>
                </div>
                
                <div class="form-group">
                    <label for="geslo2">Potrdi geslo</label>
                    <input type="password" name="geslo2" id="geslo2" class="form-control" placeholder="************" required>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-user-plus me-2"></i>Registriraj se
                </button>
                
                <a href="prijava.php" class="link">Že imaš račun? Prijavi se</a>
            </form>
        </div>
    </div>

    <script>
        // Create floating elements
        function createFloatingElements() {
            const container = document.getElementById('floatingElements');
            const colors = ['rgba(106, 17, 203, 0.3)', 'rgba(37, 117, 252, 0.3)', 'rgba(255, 255, 255, 0.2)'];
            
            for (let i = 0; i < 15; i++) {
                const element = document.createElement('div');
                element.classList.add('floating-element');
                
                // Random properties
                const size = Math.random() * 60 + 20;
                const left = Math.random() * 100;
                const animationDuration = Math.random() * 30 + 20;
                const animationDelay = Math.random() * 5;
                const color = colors[Math.floor(Math.random() * colors.length)];
                
                element.style.width = `${size}px`;
                element.style.height = `${size}px`;
                element.style.left = `${left}%`;
                element.style.animationDuration = `${animationDuration}s`;
                element.style.animationDelay = `${animationDelay}s`;
                element.style.background = color;
                
                container.appendChild(element);
            }
        }
        
        // Toggle letnik field based on role selection
        document.getElementById('dijak').addEventListener('change', toggleLetnik);
        document.getElementById('učitelj').addEventListener('change', toggleLetnik);
        
        function toggleLetnik() {
            const dijakRadio = document.getElementById('dijak');
            const letnikContainer = document.getElementById('letnik-container');
            
            if (dijakRadio.checked) {
                letnikContainer.style.display = 'block';
            } else {
                letnikContainer.style.display = 'none';
            }
        }
        
        // Initialize on page load
        window.onload = function() {
            createFloatingElements();
            toggleLetnik();
        };
    </script>
</body>
</html>

