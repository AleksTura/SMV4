<?php
session_start();
require_once 'db_connection.php'; // Include your database connection

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

// Get subject name and verify user has access to this subject
$subject_name = "";
$materials = [];

if ($user_type == 'ucenec') {
    // For students - verify they are enrolled in this subject
    $sql = "SELECT p.Ime_predmeta 
            FROM Predmet p 
            INNER JOIN Dij_predmet dp ON p.Id_predmeta = dp.Id_predmeta 
            WHERE dp.Id_dijaka = ? AND p.Id_predmeta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $subject_data = $result->fetch_assoc();
        $subject_name = $subject_data['Ime_predmeta'];
        
        // Note: You need to create a Gradivo table for materials
        // This is a placeholder - adjust based on your actual materials table
        $material_sql = "SELECT * FROM Gradivo WHERE Id_predmeta = ? ORDER BY Datum_objave DESC";
        $material_stmt = $conn->prepare($material_sql);
        $material_stmt->bind_param("i", $subject_id);
        $material_stmt->execute();
        $material_result = $material_stmt->get_result();
        
        while ($row = $material_result->fetch_assoc()) {
            $materials[] = $row;
        }
    } else {
        die("You don't have access to this subject");
    }
    
} elseif ($user_type == 'ucitelj') {
    // For teachers - verify they teach this subject
    $sql = "SELECT p.Ime_predmeta 
            FROM Predmet p 
            INNER JOIN Uci_predmet up ON p.Id_predmeta = up.Id_predmeta 
            WHERE up.Id_ucitelja = ? AND p.Id_predmeta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $subject_data = $result->fetch_assoc();
        $subject_name = $subject_data['Ime_predmeta'];
        
        // Note: You need to create a Gradivo table for materials
        // This is a placeholder - adjust based on your actual materials table
        $material_sql = "SELECT * FROM Gradivo WHERE Id_predmeta = ? ORDER BY Datum_objave DESC";
        $material_stmt = $conn->prepare($material_sql);
        $material_stmt->bind_param("i", $subject_id);
        $material_stmt->execute();
        $material_result = $material_stmt->get_result();
        
        while ($row = $material_result->fetch_assoc()) {
            $materials[] = $row;
        }
    } else {
        die("You don't teach this subject");
    }
} else {
    die("Invalid user type");
}

// Handle file download
if (isset($_GET['download']) && isset($_GET['material_id'])) {
    $material_id = $_GET['material_id'];
    
    // Verify the material belongs to the subject
    $verify_sql = "SELECT * FROM Gradivo WHERE Id_gradiva = ? AND Id_predmeta = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $material_id, $subject_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows > 0) {
        $material = $verify_result->fetch_assoc();
        $file_path = $material['Potek_do_datoteke'];
        $file_name = $material['Ime_gradiva'];
        
        if (file_exists($file_path)) {
            // Determine content type based on file extension
            $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
            
            switch($file_extension) {
                case 'pdf':
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: inline; filename="' . $file_name . '"');
                    break;
                case 'doc':
                case 'docx':
                    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                    header('Content-Disposition: attachment; filename="' . $file_name . '"');
                    break;
                case 'txt':
                    header('Content-Type: text/plain');
                    header('Content-Disposition: inline; filename="' . $file_name . '"');
                    break;
                default:
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . $file_name . '"');
            }
            
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        } else {
            echo "File not found";
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
    
    <div class="list-container">
        <h2 class="form-title">Gradivo</h2>
        <ul class="subject-list">
            <?php
            // Check if there are materials
            if (count($materials) > 0) {
                // Output each material
                foreach($materials as $material) {
                    $file_extension = strtolower(pathinfo($material['Potek_do_datoteke'], PATHINFO_EXTENSION));
                    $icon_class = '';
                    
                    // Set appropriate icon based on file type
                    switch($file_extension) {
                        case 'pdf':
                            $icon_class = 'fas fa-file-pdf';
                            break;
                        case 'doc':
                        case 'docx':
                            $icon_class = 'fas fa-file-word';
                            break;
                        case 'txt':
                            $icon_class = 'fas fa-file-alt';
                            break;
                        case 'zip':
                        case 'rar':
                            $icon_class = 'fas fa-file-archive';
                            break;
                        default:
                            $icon_class = 'fas fa-file';
                    }
                    
                    echo "<li class='subject-item'>";
                    echo "<i class='$icon_class subject-icon'></i>";
                    echo "<span>" . htmlspecialchars($material['Ime_gradiva']) . "</span>";
                    echo "<div class='material-actions'>";
                    
                    // Download/view link
                    $download_url = "?download=true&material_id=" . $material['Id_gradiva'] . "&subject_id=" . $subject_id;
                    echo "<a href='" . htmlspecialchars($download_url) . "' class='download-btn'>";
                    
                    if ($file_extension == 'pdf') {
                        echo "<i class='fas fa-eye'></i> View";
                    } else {
                        echo "<i class='fas fa-download'></i> Download";
                    }
                    
                    echo "</a>";
                    echo "</div>";
                    echo "</li>";
                }
            } else {
                echo "<li class='subject-item'>Trenutno ni gradiva za ta predmet.</li>";
            }
            ?>
        </ul>
        
        <?php if ($user_type == 'ucitelj'): ?>
        <div class="teacher-actions">
            <a href="dodaj_gradivo.php?subject_id=<?php echo $subject_id; ?>" class="add-subject-btn">
                <i class="fas fa-plus"></i> Dodaj novo gradivo
            </a>
        </div>
        <?php endif; ?>
    </div>
    </body>
    <footer>

    </footer>
</html>
<?php
// Close the database connection
$conn->close();
?>