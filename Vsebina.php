<?php
session_start();
$servername = "localhost"; 
$username = "root";       
$password = "";           
$dbname = "smv4";   

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in and is teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'ucitelj') {
    header('Location: prijava.php');
    exit;
}

// Get user ID and subject ID
$user_id = $_SESSION['user_id'];
$subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : null;

if (!$subject_id) {
    die("Subject ID not specified");
}

// Verify teacher teaches this subject
$sql = "SELECT p.Ime_predmeta 
        FROM Predmet p 
        INNER JOIN Uci_predmet up ON p.Id_predmeta = up.Id_predmeta 
        WHERE up.Id_ucitelja = ? AND p.Id_predmeta = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $subject_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("You don't teach this subject");
}

$subject_data = $result->fetch_assoc();
$subject_name = $subject_data['Ime_predmeta'];

// Handle form submission for new content
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_content'])) {
    $snov = trim($_POST['snov']);
    
    if (empty($snov)) {
        $error_message = "Vnesite ime snovi!";
    } else {
        // Get next available Id_vsebine
        $id_sql = "SELECT MAX(Id_vsebine) as max_id FROM Vsebina";
        $id_result = $conn->query($id_sql);
        $max_id = $id_result->fetch_assoc()['max_id'];
        $new_id = $max_id ? $max_id + 1 : 1;
        
        // Insert new content
        $insert_sql = "INSERT INTO Vsebina (Id_vsebine, Id_ucitelja, Id_predmeta, snov) 
                      VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iiis", $new_id, $user_id, $subject_id, $snov);
        
        if ($insert_stmt->execute()) {
            $success_message = "Snov uspešno dodana!";
            // Clear the form
            $_POST['snov'] = "";
        } else {
            $error_message = "Napaka pri dodajanju snovi: " . $conn->error;
        }
    }
}

// Get existing themes for this subject
$themes_sql = "SELECT * FROM Vsebina 
               WHERE Id_predmeta = ? AND Id_ucitelja = ?
               ORDER BY Id_vsebine";
$themes_stmt = $conn->prepare($themes_sql);
$themes_stmt->bind_param("ii", $subject_id, $user_id);
$themes_stmt->execute();
$themes_result = $themes_stmt->get_result();
$existing_themes = [];

while ($row = $themes_result->fetch_assoc()) {
    $existing_themes[] = $row;
}
?>

<html>
<head>
    <title>Dodaj snov - <?php echo htmlspecialchars($subject_name); ?></title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="izgled.css">
    <meta name="author" content="Špela Zeme">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="floating-elements" id="floatingElements"></div>
    
    <div class="top-bar">
        <h1>Dodaj novo snov - <?php echo htmlspecialchars($subject_name); ?></h1>
        <a href="profil.php" class="user-icon">
            <i class="fas fa-user"></i>
        </a>
    </div>
    
    <div class="glass-card">
        <div class="header-card">
            <h2><i class="fas fa-book-open"></i> Nova snov</h2>
        </div>
        
        <div class="VpisBox">
            <?php if ($success_message): ?>
                <div class="success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                
                <div class="form-group">
                    <label for="snov"><i class="fas fa-tag"></i> Ime snovi:</label>
                    <input type="text" id="snov" name="snov" class="form-control" 
                           value="<?php echo isset($_POST['snov']) ? htmlspecialchars($_POST['snov']) : ''; ?>" 
                           placeholder="Vnesite ime nove snovi" required>
                </div>
                
                <button type="submit" name="add_content" class="btn">
                    <i class="fas fa-plus"></i> Dodaj snov
                </button>
            </form>
            
            <a href="stranPredmeta.php?subject_id=<?php echo $subject_id; ?>" class="link">
                <i class="fas fa-arrow-left"></i> Nazaj na predmet
            </a>
        </div>
    </div>
    
    <!-- Display existing themes -->
    <?php if (count($existing_themes) > 0): ?>
    <div class="list-container" style="margin-top: 30px;">
        <h2 class="form-title">Obstoječe snovi</h2>
        <ul class="subject-list">
            <?php foreach($existing_themes as $theme): ?>
                <li class="subject-item">
                    <i class="fas fa-folder subject-icon"></i>
                    <span><?php echo htmlspecialchars($theme['snov']); ?></span>
                    <div class="material-actions">
                        <a href="stranPredmeta.php?theme_id=<?php echo $theme['Id_vsebine']; ?>&subject_id=<?php echo $subject_id; ?>" 
                           class="download-btn">
                            <i class="fas fa-eye"></i> Ogled nalog
                        </a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>