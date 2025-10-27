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
    $user_type = $_POST['user_type'] ?? 'ucenec'; // Dodano: tip uporabnika

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
            if ($user_type === 'ucenec') {
                // Prijava za učenca
                $sql = "SELECT Id_dijaka, geslo FROM Ucenec WHERE ime = ? AND priimek = ? LIMIT 1"; 
                $id_column = 'Id_dijaka';
            } else if ($user_type == 'ucitelj'){
                // Prijava za učitelja
                $sql = "SELECT Id_ucitelja, geslo FROM Ucitelj WHERE ime = ? AND priimek = ? LIMIT 1"; 
                $id_column = 'Id_ucitelja';
            }
            else if ($user_type == 'admin'){
                // Prijava za admina
                $sql = "SELECT Id_admin, geslo FROM Admin WHERE ime = ? AND priimek = ? LIMIT 1"; 
                $id_column = 'Id_admin';
            }
            
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
                
                // Verify password - POSEBNO ZA ADMINA
                $passwordValid = false;
                
                if ($user_type == 'admin') {
                    // Za admina preverjamo nehashirano geslo
                    $passwordValid = ($password_input === $storedHash);
                } else {
                    // Za učence in učitelje uporabljamo password_verify
                    $passwordValid = password_verify($password_input, $storedHash);
                }
                
                if ($passwordValid) {
                    // Set session variables
                    $_SESSION['user_id'] = $row[$id_column];  
                    $_SESSION['user_type'] = $user_type;
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_name'] = $ime . ' ' . $priimek; // Shranimo ime za prikaz

                    if ($user_type == 'admin') {
                        echo "<script>
                        alert('Prijava uspešna - Admin');
                        window.location.href = 'admin.php';
                        </script>";
                    } else {
                        echo "<script>
                        alert('Prijava uspešna - " . ($user_type === 'ucenec' ? 'Učenec' : 'Učitelj') . "');
                        window.location.href = 'prvastran.php';
                        </script>";
                    }
                    exit;
                } else {
                    $error_message = "Napačno geslo";
                }
            } else {
                $error_message = ($user_type === 'ucenec' 
                    ? "Učenec s tem imenom in priimkom ne obstaja" 
                    : ($user_type === 'ucitelj' 
                        ? "Učitelj s tem imenom in priimkom ne obstaja"
                        : "Admin s tem imenom in priimkom ne obstaja"));
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
                <!-- Dodano: Izbira tipa uporabnika -->
                <div class="form-group">
                    <label for="user_type">Prijavim se kot:</label>
                    <select id="user_type" name="user_type" class="form-control" required>
                        <option value="ucenec" <?php echo (($_POST['user_type'] ?? 'ucenec') === 'ucenec') ? 'selected' : ''; ?>>Učenec</option>
                        <option value="ucitelj" <?php echo (($_POST['user_type'] ?? '') === 'ucitelj') ? 'selected' : ''; ?>>Učitelj</option>
                        <option value="admin" <?php echo (($_POST['user_type'] ?? '') === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

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