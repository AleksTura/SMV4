<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: prijava.php');
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smv4";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user info from session
$user_id = $_SESSION['UserId'];
$user_type = $_SESSION['user_type'];

// Fetch subjects based on user type
if ($user_type == 'ucenec') {
    // For students - get subjects they are enrolled in
    $sql = "SELECT p.Id_predmeta, p.Ime_predmeta, up.snov 
            FROM Predmet p 
            INNER JOIN Dij_predmet dp ON p.Id_predmeta = dp.Id_predmeta 
            INNER JOIN Uci_predmet up ON dp.Id_ucitelja = up.Id_ucitelja AND dp.Id_predmeta = up.Id_predmeta
            WHERE dp.Id_dijaka = ? 
            ORDER BY p.Ime_predmeta";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
} elseif ($user_type == 'ucitelj') {
    // For teachers - get subjects they teach
    $sql = "SELECT p.Id_predmeta, p.Ime_predmeta, up.snov 
            FROM Predmet p 
            INNER JOIN Uci_predmet up ON p.Id_predmeta = up.Id_predmeta 
            WHERE up.Id_ucitelja = ? 
            ORDER BY p.Ime_predmeta";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
} else {
    die("Invalid user type");
}

// Handle subject click - store subject ID in session and redirect
if (isset($_POST['subject_id'])) {
    $_SESSION['subject_id'] = $_POST['subject_id'];
    header('Location: predmet.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Spletna učilnica</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="izgled.css">
    <meta name="author" content="Špela Zeme">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="floating-elements" id="floatingElements"></div>
    
    <div class="top-bar">
        <h1>Spletna učilnica</h1>
        <div class="user-info">
            <span class="welcome-text">Pozdravljen, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Uporabnik'); ?></span>
            <a href="profil.php" class="user-icon">
                <i class="fas fa-user"></i>
            </a>
        </div>
    </div>
    
    <div class="list-container">
        <h2 class="form-title">Moji predmeti</h2>
        <ul class="subject-list">
            <?php
            // Check if there are results
            if ($result->num_rows > 0) {
                // Output each row of data
                while($row = $result->fetch_assoc()) {
                    echo "<li class='subject-item'>";
                    echo "<form method='POST' action=''>";
                    echo "<input type='hidden' name='subject_id' value='" . $row['Id_predmeta'] . "'>";
                    echo "<button type='submit' class='subject-link'>";
                    echo "<i class='fas fa-book subject-icon'></i>";
                    echo "<span class='subject-name'>" . htmlspecialchars($row['Ime_predmeta']) . "</span>";
                    if (!empty($row['snov'])) {
                        echo "<span class='subject-topic'>(" . htmlspecialchars($row['snov']) . ")</span>";
                    }
                    echo "</button>";
                    echo "</form>";
                    echo "</li>";
                }
            } else {
                echo "<li class='subject-item'>";
                echo "<div class='subject-link'>";
                echo "<i class='fas fa-exclamation-circle subject-icon'></i>";
                echo "<span class='subject-name'>";
                if ($user_type == 'ucenec') {
                    echo "Niste vpisani na noben predmet.";
                } else {
                    echo "Nimate dodeljenih predmetov.";
                }
                echo "</span>";
                echo "</div>";
                echo "</li>";
            }
            ?>
        </ul>
        
        <?php if ($user_type == 'ucitelj'): ?>
        <div class="teacher-actions">
            <a href="dodaj_predmet.php" class="add-subject-btn">
                <i class="fas fa-plus"></i> Dodaj nov predmet
            </a>
        </div>
        <?php endif; ?>
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
        
        // Initialize on page load
        window.onload = createFloatingElements;
    </script>
</body>
</html>
<?php
// Close the database connection
$conn->close();
?>