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
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = $_POST['username'] ?? '';
    $user_type = $_POST['user_type'] ?? 'ucenec';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];
    
    // Validation
    if (empty($username_input)) {
        $errors[] = "Polje za uporabniško ime mora biti izpolnjeno";
    }
    if (empty($new_password)) {
        $errors[] = "Polje za novo geslo mora biti izpolnjeno";
    }
    if (empty($confirm_password)) {
        $errors[] = "Polje za potrditev gesla mora biti izpolnjeno";
    }
    if ($new_password !== $confirm_password) {
        $errors[] = "Gesli se ne ujemata";
    }
    if (strlen($new_password) < 6) {
        $errors[] = "Geslo mora vsebovati vsaj 6 znakov";
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
            // Preveri ali uporabnik obstaja
            if ($user_type === 'ucenec') {
                $check_sql = "SELECT Id_dijaka FROM Ucenec WHERE ime = ? AND priimek = ? LIMIT 1"; 
                $update_sql = "UPDATE Ucenec SET geslo = ? WHERE ime = ? AND priimek = ?";
            } else if ($user_type == 'ucitelj') {
                $check_sql = "SELECT Id_ucitelja FROM Ucitelj WHERE ime = ? AND priimek = ? LIMIT 1"; 
                $update_sql = "UPDATE Ucitelj SET geslo = ? WHERE ime = ? AND priimek = ?";
            } else if ($user_type == 'admin') {
                $check_sql = "SELECT Id_admin FROM Admin WHERE ime = ? AND priimek = ? LIMIT 1"; 
                $update_sql = "UPDATE Admin SET geslo = ? WHERE ime = ? AND priimek = ?";
            }
            
            // Preveri obstoj uporabnika
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("ss", $ime, $priimek);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                // Uporabnik obstaja, posodobi geslo
                $stmt->close();
                
                $stmt = $conn->prepare($update_sql);
                
                // Za admina shrani nehashirano geslo, za ostale hashiraj
                if ($user_type == 'admin') {
                    $hashed_password = $new_password; // Admin - nehashirano
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                }
                
                $stmt->bind_param("sss", $hashed_password, $ime, $priimek);
                
                if ($stmt->execute()) {
                    $success_message = "Geslo je bilo uspešno spremenjeno!";
                    // Počakaj 2 sekundi in preusmeri na prijavo
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'prijava.php';
                        }, 2000);
                    </script>";
                } else {
                    $error_message = "Napaka pri posodabljanju gesla: " . $conn->error;
                }
            } else {
                $error_message = ($user_type === 'ucenec' 
                    ? "Učenec s tem imenom in priimkom ne obstaja" 
                    : ($user_type === 'ucitelj' 
                        ? "Učitelj s tem imenom in priimkom ne obstaja"
                        : "Admin s tem imenom in priimkom ne obstaja"));
            }
            
            if ($stmt) {
                $stmt->close();
            }
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
    <title>Pozabljeno geslo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="floating-elements" id="floatingElements"></div>
    
    <div class="glass-card">
        <div class="header-card">
            <h1>Pozabljeno geslo</h1>
        </div>
        
        <div class="VpisBox">
            <form action="pozabljeno_geslo.php" method="POST" id="passwordResetForm">
                <div class="form-group">
                    <label for="user_type">Prijavljen sem kot:</label>
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
                    <label for="new_password">Novo geslo</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Novo geslo" required>
                    <small style="color: #666; font-size: 12px;">Geslo mora vsebovati vsaj 6 znakov</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Potrdite novo geslo</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Ponovite novo geslo" required>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-key me-2"></i>Zamenjaj geslo
                </button>
                
                <a href="prijava.php" class="link">Nazaj na prijavo</a>
            </form>
        </div>
    </div>

    <script>
        // Show messages
        <?php if (!empty($error_message)): ?>
        alert("Napaka:\n<?php echo $error_message; ?>");
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
        alert("<?php echo $success_message; ?>");
        <?php endif; ?>

        // Create floating elements
        function createFloatingElements() {
            const container = document.getElementById('floatingElements');
            const colors = ['rgba(106, 17, 203, 0.3)', 'rgba(37, 117, 252, 0.3)', 'rgba(255, 255, 255, 0.2)'];
            
            for (let i = 0; i < 15; i++) {
                const element = document.createElement('div');
                element.classList.add('floating-element');
                
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
        
        // Password validation
        document.getElementById('passwordResetForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword.length < 6) {
                alert('Geslo mora vsebovati vsaj 6 znakov');
                e.preventDefault();
                return;
            }
            
            if (newPassword !== confirmPassword) {
                alert('Gesli se ne ujemata');
                e.preventDefault();
                return;
            }
        });
        
        window.onload = createFloatingElements;
    </script>
</body>
</html>