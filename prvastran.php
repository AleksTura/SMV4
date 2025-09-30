<?php
session_start();
// Database connection
$servername = "localhost"; // or your DB host
$username = "root";        // your DB username
$password = "";            // your DB password
$dbname = "smv4";   // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch subjects from the database
$sql = "SELECT Ime_predmeta FROM predmet";
$result = $conn->query($sql);

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
        <a href="profil.php" class="user-icon">
            <i class="fas fa-user"></i>
        </a>
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
                    echo "<i class='fas fa-book subject-icon'></i>";
                    echo "<span>" . htmlspecialchars($row['Ime_predmeta']) . "</span>";
                    echo "</li>";
                }
            } else {
                echo "<li class='subject-item'>No subjects found.</li>";
            }
            ?>
        </ul>
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
