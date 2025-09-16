<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $ime = trim($_POST['ime'] ?? '');
    $priimek = trim($_POST['priimek'] ?? '');
    $naziv = $_POST['naziv'] ?? '';
    $letnik = trim($_POST['letnik'] ?? '');
    $geslo1 = $_POST['geslo1'] ?? '';
    $geslo2 = $_POST['geslo2'] ?? '';

    
    $errors = [];

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

    if (!empty($errors)) {
       
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
        echo "<p><a href='javascript:history.back()'>Nazaj na obrazec</a></p>";
        exit;
    }

    $hashedPassword = password_hash($geslo1, PASSWORD_DEFAULT);

    // TO DO: Save user data to database here (e.g., MySQL)
    // TO DO: Open new site based on user

} else {
    // If form not submitted, redirect or show message
    echo "Obrazec ni bil pravilno poslan.";
}
?>
