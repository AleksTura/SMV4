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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: prijava.php');
    exit;
}

// Get user ID and subject ID from session
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$subject_id = isset($_SESSION['subject_id']) ? $_SESSION['subject_id'] : null;

// If subject_id is not in session, try to get it from GET or POST
if (!$subject_id) {
    $subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : (isset($_POST['subject_id']) ? $_POST['subject_id'] : null);
}

if (!$subject_id) {
    die("Subject ID not specified");
}

// Get subject name and verify user has access to this subject
$subject_name = "";
$themes = []; // Changed from $materials to $themes

if ($user_type == 'ucenec') {
    // For students - verify they are enrolled in this subject
    $sql = "SELECT p.Ime_predmeta 
            FROM Predmet p INNER JOIN Dij_predmet dp 
            ON p.Id_predmeta = dp.Id_predmeta 
            WHERE dp.Id_dijaka = ? 
            AND p.Id_predmeta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $subject_data = $result->fetch_assoc();
        $subject_name = $subject_data['Ime_predmeta'];
        
        // Get themes (vsebina) for this subject
        $theme_sql = "SELECT v.*, up.Id_ucitelja 
                     FROM Vsebina v INNER JOIN Uci_predmet up 
                     ON v.Id_ucitelja = up.Id_ucitelja AND v.Id_predmeta = up.Id_predmeta 
                     WHERE v.Id_predmeta = ? 
                     ORDER BY v.Id_vsebine";
        $theme_stmt = $conn->prepare($theme_sql);
        $theme_stmt->bind_param("i", $subject_id);
        $theme_stmt->execute();
        $theme_result = $theme_stmt->get_result();
        
        while ($row = $theme_result->fetch_assoc()) {
            $themes[] = $row;
        }
    } else {
        die("You don't have access to this subject");
    }
    
} elseif ($user_type == 'ucitelj') {
    // For teachers - verify they teach this subject
    $sql = "SELECT p.Ime_predmeta 
            FROM Predmet p INNER JOIN Uci_predmet up 
            ON p.Id_predmeta = up.Id_predmeta 
            WHERE up.Id_ucitelja = ? 
            AND p.Id_predmeta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $subject_data = $result->fetch_assoc();
        $subject_name = $subject_data['Ime_predmeta'];
        
        // Get themes (vsebina) for this subject taught by this teacher
        $theme_sql = "SELECT v.* 
                     FROM Vsebina v 
                     WHERE v.Id_predmeta = ? AND v.Id_ucitelja = ?
                     ORDER BY v.Id_vsebine";
        $theme_stmt = $conn->prepare($theme_sql);
        $theme_stmt->bind_param("ii", $subject_id, $user_id);
        $theme_stmt->execute();
        $theme_result = $theme_stmt->get_result();
        
        while ($row = $theme_result->fetch_assoc()) {
            $themes[] = $row;
        }
    } else {
        die("You don't teach this subject");
    }
} else {
    die("Invalid user type");
}

// Handle exercises display for a specific theme
if (isset($_GET['theme_id'])) {
    $theme_id = $_GET['theme_id'];
    
    // Verify the theme belongs to the subject
    $verify_sql = "SELECT * FROM Vsebina WHERE Id_vsebine = ? AND Id_predmeta = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $theme_id, $subject_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows > 0) {
        $selected_theme = $verify_result->fetch_assoc();
        
        // Get exercises for this theme
        $exercise_sql = "SELECT * FROM Naloga WHERE Id_vsebine = ? ORDER BY Id_naloge";
        $exercise_stmt = $conn->prepare($exercise_sql);
        $exercise_stmt->bind_param("i", $theme_id);
        $exercise_stmt->execute();
        $exercises_result = $exercise_stmt->get_result();
        $exercises = [];
        
        while ($row = $exercises_result->fetch_assoc()) {
            $exercises[] = $row;
        }
    }
}
?>

<html>
    <head>
    <title>Predmet</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="izgled.css">
    <meta name="author" content="Špela Zeme">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>
    <div class="floating-elements" id="floatingElements"></div>
    
    <div class="top-bar">
        <h1><?php echo htmlspecialchars($subject_name); ?></h1>
        <a href="profil.php" class="user-icon">
            <i class="fas fa-user"></i>
        </a>
    </div>
    
    <?php if (!isset($_GET['theme_id'])): ?>
    <!-- Display themes list -->
    <div class="list-container">
        <h2 class="form-title">Snovi</h2>
        <ul class="subject-list">
            <?php
            // Check if there are themes
            if (count($themes) > 0) {
                // Output each theme
                foreach($themes as $theme) {
                    echo "<li class='subject-item'>";
                    echo "<i class='fas fa-folder subject-icon'></i>";
                    echo "<span>" . htmlspecialchars($theme['snov']) . "</span>";
                    echo "<div class='material-actions'>";
                    
                    // View exercises link
                    $exercises_url = "?theme_id=" . $theme['Id_vsebine'] . "&subject_id=" . $subject_id;
                    echo "<a href='" . htmlspecialchars($exercises_url) . "' class='download-btn'>";
                    echo "<i class='fas fa-tasks'></i> Naloge";
                    echo "</a>";
                    echo "</div>";
                    echo "</li>";
                }
            } else {
                echo "<li class='subject-item'>Trenutno ni tem za ta predmet.</li>";
            }
            ?>
            <a href="prvastran.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Nazaj na predmete
            </a>
        </ul>
        
        <?php if ($user_type == 'ucitelj'): ?>
        <div class="teacher-actions">
            <a href="Vsebina.php?subject_id=<?php echo $subject_id; ?>" class="add-subject-btn">
                <i class="fas fa-plus"></i> Dodaj novo snov
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <?php else: ?>
    <!-- Display exercises for selected theme -->
    <div class="list-container">
        <h2 class="form-title">
            Naloge za: <?php echo htmlspecialchars($selected_theme['snov']); ?>
            
        </h2>
        
        <ul class="subject-list">
            <?php
            // Check if there are exercises
            if (count($exercises) > 0) {
                // Output each exercise
                foreach($exercises as $exercise) {
                    echo "<li class='subject-item'>";
                    echo "<i class='fas fa-file-alt subject-icon'></i>";
                    echo "<div class='exercise-content'>";
                    echo "<strong>" . htmlspecialchars($exercise['opis_naloge']) . "</strong>";
                    if (!empty($exercise['komentar'])) {
                        echo "<p class='exercise-comment'>" . htmlspecialchars($exercise['komentar']) . "</p>";
                    }
                    echo "</div>";
                    echo "</li>";
                }
            } else {
                echo "<li class='subject-item'>Trenutno ni nalog za to temo.</li>";
            }
            ?>
            <a href="?subject_id=<?php echo $subject_id; ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Nazaj na snovi
            </a>
        </ul>
        
        <?php if ($user_type == 'ucitelj'): ?>
        <div class="teacher-actions">
            <a href="Naloga.php?theme_id=<?php echo $theme_id; ?>&subject_id=<?php echo $subject_id; 
            ?>&naloga_id=<?php echo $exercise['Id_naloge']; ?>" class="add-subject-btn">
                <i class="fas fa-plus"></i> Dodaj novo nalogo
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    </body>
    <footer>

    </footer>
</html>
<?php
// Close the database connection
$conn->close();
?>