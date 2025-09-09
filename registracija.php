<?php
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form inputs safely
    $ime = trim($_POST['ime'] ?? '');
    $priimek = trim($_POST['priimek'] ?? '');
    $naziv = $_POST['naziv'] ?? '';
    $letnik = trim($_POST['letnik'] ?? '');
    $geslo1 = $_POST['geslo1'] ?? '';
    $geslo2 = $_POST['geslo2'] ?? '';

    // Simple validation
    $errors = [];

    if (empty($ime)) {
        $errors[] = "Ime je obvezno.";
    }
    if (empty($priimek)) {
        $errors[] = "Priimek je obvezen.";
    }
    if (!in_array($naziv, ['Učitelj', 'Dijak'])) {
        $errors[] = "Izberite veljaven naziv.";
    }
    if ($naziv === 'Dijak' && empty($letnik)) {
        $errors[] = "Letnik je obvezen za dijake.";
    }
    if (empty($geslo1) || empty($geslo2)) {
        $errors[] = "Geslo in potrditveno geslo morata biti izpolnjena.";
    } elseif ($geslo1 !== $geslo2) {
        $errors[] = "Gesli se ne ujemata.";
    }

    if (!empty($errors)) {
        // Show errors (simple example)
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
        echo "<p><a href='javascript:history.back()'>Nazaj na obrazec</a></p>";
        exit;
    }

    // Password hashing (important!)
    $hashedPassword = password_hash($geslo1, PASSWORD_DEFAULT);

    // TODO: Save user data to database here (e.g., MySQL)
    // For now, just display the submitted info (excluding password)
    echo "<h2>Uspešna registracija</h2>";
    echo "<p>Ime: " . htmlspecialchars($ime) . "</p>";
    echo "<p>Priimek: " . htmlspecialchars($priimek) . "</p>";
    echo "<p>Naziv: " . htmlspecialchars($naziv) . "</p>";
    if ($naziv === 'Dijak') {
        echo "<p>Letnik: " . htmlspecialchars($letnik) . "</p>";
    }

    // Never show passwords back to user!

} else {
    // If form not submitted, redirect or show message
    echo "Obrazec ni bil pravilno poslan.";
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
        <h1>Registracija</h1>
        <form action="welcome.php" method="POST">
            <label for="ime">Ime</label>
                <input type="text" id="ime" placeholder="Janez"><br>
            <label for="Priimek">Priimek</label>
                <input type="text" id="Priimek" placeholder="Novak"><br>
            <label for="Naziv">Izberi naziv</label><br>
                    <input type="radio" id="Učitelj" name="Naziv" value="Učitelj">
                <label for="Učitelj">Učitelj</label><br>
                    <input type="radio" id="Dijak" name="Naziv" value="Dijak" checked>
                <label for="Dijak">Dijak</label><br>
            <div id="letnik-container">
                <label for="Letnik">Letnik</label>
                    <input type="text" id="Letnik" placeholder="R3B"><br>
            </div>
            <label for="geslo1">Geslo</label><br>
                <input type="text" id="Geslo1" placeholder="************"><br>
            <label for="geslo2">Potrdi geslo</label><br>
                <input type="text" id="Geslo2" placeholder="************"><br>
            <input type="button" value="Registriraj se">
        </form>
    </body>
</html>
<script>
    // Pripni event listenerje na radio gumbe
    document.getElementById('Dijak').addEventListener('change', toggleLetnik);
    document.getElementById('Učitelj').addEventListener('change', toggleLetnik);

    // Pokliči funkcijo ob nalaganju strani, da nastavi pravilno stanje
    window.onload = toggleLetnik;
    
        // Funkcija, ki pokaže/skrije letnik glede na izbran radio gumb
        function toggleLetnik() {
        const dijakRadio = document.getElementById('Dijak');
        const letnikContainer = document.getElementById('letnik-container');

        if (dijakRadio.checked) {
            letnikContainer.style.display = 'block';
        } else {
            letnikContainer.style.display = 'none';
        }
    }
</script>
