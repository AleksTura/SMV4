<?php
session_start();
$servername = "localhost"; 
$username = "root";       
$password = "";           
$dbname = "smv4";   

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Povezava z bazo ni uspela: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = $_POST['username'] ?? '';
    $password_input = $_POST['password'] ?? '';

    $errors = [];
    
    // Validation
    if (empty($username_input)) {
        $errors[] = "Polje za uporabniško ime mora biti izpolnjeno";
    }
    if (empty($password_input)) {
        $errors[] = "Polje za geslo mora biti izpolnjeno";
    }

    if (!empty($errors)) {
        $error_message = implode("\\n", $errors);
    } else {
        // Parse username (format: "name surname")
        $nameParts = explode(' ', trim($username_input), 2);
        $ime = $nameParts[0] ?? '';
        $priimek = $nameParts[1] ?? '';

        if (empty($priimek)) {
            $error_message = "Vnesite polno ime in priimek (npr: Janez Novak)";
        } else {
            // Prepare SQL statement
            $sql = "SELECT Id_dijaka, geslo FROM Ucenec WHERE ime = ? AND priimek = ? LIMIT 1"; 
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                die("Napaka pri pripravi poizvedbe: " . $conn->error);
            }
            
            $stmt->bind_param("ss", $ime, $priimek);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $storedHash = $row['geslo'];
                
                // Verify password
                if (password_verify($password_input, $storedHash)) {
                    // Set session variables
                    if ($result && $row = $result->fetch_assoc()) {
                        $_SESSION["UserId"] = $row['Id_dijaka'];  // Shranimo ID v sejo
                    }
                    $_SESSION['user_name'] = $ime . ' ' . $priimek;
                    $_SESSION['logged_in'] = true;
                    
                    echo "<script>
                    alert('Prijava uspešna');
                    window.location.href = 'prvastran.php';
                    </script>";
                    exit;
                } else {
                    $error_message = "Napačno geslo";
                }
            } else {
                $error_message = "Uporabnik s tem imenom in priimkom ne obstaja";
            }
            
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="izgled.css">
    <title>Prijava</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="floating-elements" id="floatingElements"></div>
    
    <div class="glass-card">
        <div class="header-card">
            <h1>Prijava</h1>
        </div>
        
        <div class="VpisBox">
            <form action="prijava.php" method="POST" id="loginForm">
                <div class="form-group">
                    <label for="username">Ime in priimek</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Vnesite ime in priimek" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    <small style="color: #666; font-size: 12px;">Vnesite ime in priimek (npr: Janez Novak)</small>
                </div>
               
                <div class="form-group">
                    <label for="password">Geslo</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Geslo" required>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt me-2"></i>Prijava
                </button>
                
                <a href="#" class="link">Pozabljeno geslo?</a>
                <a href="registracija.php" class="link">Še nimaš računa? Registriraj se</a>
            </form>
        </div>
    </div>

    <script>
        // Show error popup if there's an error message
        <?php if (!empty($error_message)): ?>
        alert("Napaka pri prijavi:\n<?php echo $error_message; ?>");
        <?php endif; ?>

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
        
        // Initialize on page load
        window.onload = createFloatingElements;
    </script>
</body>
</html>