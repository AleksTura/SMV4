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

// Preveri, ali je uporabnik prijavljen
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: prijava.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Preveri, ali so podani parametri za nalogo
if (isset($_GET['naloga_id'])) {
    $naloga_id = $_GET['naloga_id'];
} else {
    die("Manjkajoči parameter naloga_id");
}

$submission_success = false;
$error_message = "";

// UČITELJ - sestavljanje naloge
if ($user_type === 'ucitelj' && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_assignment'])) {
    $assignment_description = $_POST['assignment_description'] ?? '';
    $subject_content_id = $_POST['subject_content_id'] ?? '';
    $instructions = $_POST['instructions'] ?? '';
    
    // Pridobi naslednji ID za nalogo
    $sql_id = "SELECT MAX(Id_naloge) as max_id FROM Naloga";
    $result = $conn->query($sql_id);
    $row = $result->fetch_assoc();
    $next_id = ($row['max_id'] ?? 0) + 1;
    
    // Vstavi podatke v bazo (brez datoteke - samo navodila)
    $sql = "INSERT INTO Naloga (Id_naloge, Id_vsebine, opis_naloge, komentar) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("iiss", $next_id, $subject_content_id, $assignment_description, $instructions);
        
        if ($stmt->execute()) {
            $submission_success = true;
            $success_message = "Naloga je bila uspešno ustvarjena!";
        } else {
            $error_message = "Napaka pri shranjevanju v bazo: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        $error_message = "Napaka pri pripravi poizvedbe: " . $conn->error;
    }
}

// UČENEC - oddaja naloge
if ($user_type === 'ucenec' && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_assignment'])) {
    $assignment_id = $_POST['assignment_id'] ?? '';
    
    // Preveri, ali je bila naložena datoteka
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['assignment_file'];
        $file_name = $file['name'];
        $file_tmp_name = $file['tmp_name'];
        
        // Ustvari imenik za naloge, če ne obstaja
        $upload_dir = "naloge/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generiraj varno ime datoteke
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $safe_file_name = preg_replace("/[^a-zA-Z0-9\.]/", "_", pathinfo($file_name, PATHINFO_FILENAME));
        $new_file_name = $safe_file_name . '_' . uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $new_file_name;
        
        // Premakni datoteko v ciljni imenik
        if (move_uploaded_file($file_tmp_name, $file_path)) {
            // Posodobi nalogo z datoteko - samo path zapišemo v bazo
            $sql = "UPDATE Naloga SET datoteka = ? WHERE Id_naloge = ?";
            
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("si", $file_path, $assignment_id);
                
                if ($stmt->execute()) {
                    $submission_success = true;
                    $success_message = "Naloga je bila uspešno oddana!";
                    // Osveži seznam nalog, da se prikaže posodobljen status
                    header("Location: " . $_SERVER['PHP_SELF'] . "?naloga_id=" . $naloga_id);
                    exit;
                } else {
                    $error_message = "Napaka pri shranjevanju v bazo: " . $stmt->error;
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
                
                $stmt->close();
            } else {
                $error_message = "Napaka pri pripravi poizvedbe: " . $conn->error;
            }
        } else {
            $error_message = "Napaka pri premikanju datoteke";
        }
    } else {
        $error_message = "Prosimo izberite datoteko za oddajo";
    }
}

// Pridobi podatke o specifični nalogi za učenca
$assignment = null;
// Pridobi seznam vseh oddanih nalog za učitelja
$submitted_assignments = [];

if ($user_type === 'ucenec') {
    // Učenec vidi samo svojo nalogo
    $sql_assignment = "SELECT Id_naloge, opis_naloge, komentar, datoteka 
                       FROM Naloga 
                       WHERE Id_naloge = ?";
    $stmt = $conn->prepare($sql_assignment);
    if ($stmt) {
        $stmt->bind_param("i", $naloga_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $assignment = $result->fetch_assoc();
        $stmt->close();
    }
} else {
    // Učitelj vidi seznam vseh oddanih nalog za to nalogo
    $sql_submitted = "SELECT n.Id_naloge, n.opis_naloge, n.komentar, n.datoteka,
                             u.ime, u.priimek
                      FROM Naloga n
                      LEFT JOIN Uporabnik u ON n.Id_naloge = ?
                      WHERE n.Id_naloge = ? AND n.datoteka IS NOT NULL";
    $stmt = $conn->prepare($sql_submitted);
    if ($stmt) {
        $stmt->bind_param("ii", $naloga_id, $naloga_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $submitted_assignments[] = $row;
        }
        $stmt->close();
    }
    
    // Pridobi tudi osnovne podatke o nalogi
    $sql_assignment_info = "SELECT opis_naloge, komentar FROM Naloga WHERE Id_naloge = ?";
    $stmt = $conn->prepare($sql_assignment_info);
    if ($stmt) {
        $stmt->bind_param("i", $naloga_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $assignment_info = $result->fetch_assoc();
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oddaja Naloge</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a11cb;
            --secondary: #2575fc;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .header-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 25px;
            text-align: center;
        }
        
        .assignment-card {
            padding: 25px;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            color: white;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.4);
        }
        
        .alert {
            margin-bottom: 20px;
        }
        
        .assignment-item {
            border-left: 4px solid var(--primary);
            margin-bottom: 15px;
        }
        
        .download-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 6px;
            padding: 8px 15px;
            color: white;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="glass-card mb-4">
                    <div class="header-card">
                        <h1 class="h3 fw-bold mb-2">
                            <?php echo $user_type === 'ucitelj' ? 'Pregled Oddanih Nalog' : 'Oddaja Naloge'; ?>
                        </h1>
                        <p class="mb-0">
                            <?php echo $user_type === 'ucitelj' ? 'Učiteljski portal' : 'Učenska aplikacija'; ?>
                        </p>
                    </div>
                    
                    <div class="assignment-card">
                        <?php if (isset($success_message)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($error_message) && !empty($error_message)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                        </div>
                        <?php endif; ?>

                        <!-- UČITELJ - PREGLED VSEH ODDANIH NALOG -->
                        <?php if ($user_type === 'ucitelj'): ?>
                        
                        <?php if (isset($assignment_info)): ?>
                        <div class="mb-4 p-3 bg-light rounded">
                            <h5 class="fw-bold"><?php echo htmlspecialchars($assignment_info['opis_naloge']); ?></h5>
                            <div class="mb-2">
                                <strong>Navodila:</strong>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($assignment_info['komentar'])); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <h5 class="fw-bold mb-3">Seznam oddanih nalog:</h5>
                        
                        <?php if (empty($submitted_assignments)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                                <h5 class="text-muted">Še ni oddanih nalog</h5>
                                <p class="text-muted">Učenci še niso oddali nobene naloge.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($submitted_assignments as $index => $submitted): ?>
                            <div class="assignment-item p-3 bg-white rounded shadow-sm">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold mb-0">Oddana naloga #<?php echo $index + 1; ?></h6>
                                    <span class="badge bg-success">Oddano</span>
                                </div>
                                
                                <?php if (!empty($submitted['ime'])): ?>
                                <p class="mb-2"><strong>Učenec:</strong> <?php echo htmlspecialchars($submitted['ime'] . ' ' . $submitted['priimek']); ?></p>
                                <?php endif; ?>
                                
                                <p class="mb-2"><strong>Datoteka:</strong> <?php echo htmlspecialchars(basename($submitted['datoteka'])); ?></p>
                                
                                <div class="mt-3">
                                    <a href="<?php echo htmlspecialchars($submitted['datoteka']); ?>" 
                                       class="btn download-btn" 
                                       download>
                                        <i class="fas fa-download me-2"></i>Prenesi nalogo
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- UČENEC - ODAJA NALOGE -->
                        <?php else: ?>
                        <?php if (!$assignment): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                                <h5 class="text-warning">Naloga ne obstaja</h5>
                                <p class="text-muted">Naloga z ID <?php echo htmlspecialchars($naloga_id); ?> ni bila najdena.</p>
                            </div>
                        <?php else: ?>
                            <div class="assignment-item p-3 border rounded">
                                <h5 class="fw-bold"><?php echo htmlspecialchars($assignment['opis_naloge']); ?></h5>
                                <div class="mb-3">
                                    <strong>Navodila:</strong>
                                    <p><?php echo nl2br(htmlspecialchars($assignment['komentar'])); ?></p>
                                </div>
                                
                                <?php if (empty($assignment['datoteka'])): ?>
                                <form action="<?php echo $_SERVER['PHP_SELF'] . '?naloga_id=' . $naloga_id; ?>" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="assignment_id" value="<?php echo $assignment['Id_naloge']; ?>">
                                    
                                    <div class="mb-3">
                                        <label for="assignment_file" class="form-label fw-bold">Izberi datoteko:</label>
                                        <input type="file" class="form-control" name="assignment_file" id="assignment_file" required>
                                    </div>
                                    
                                    <div class="text-center">
                                        <button type="submit" name="submit_assignment" class="btn submit-btn">
                                            <i class="fas fa-paper-plane me-2"></i>ODDAJ NALOGO
                                        </button>
                                    </div>
                                </form>
                                <?php else: ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Naloga je oddana</strong>
                                    <p class="mb-0">Datoteka: <?php echo htmlspecialchars(basename($assignment['datoteka'])); ?></p>
                                    <div class="mt-2">
                                        <a href="<?php echo htmlspecialchars($assignment['datoteka']); ?>" 
                                           class="btn download-btn" 
                                           download>
                                            <i class="fas fa-download me-2"></i>Prenesi svojo nalogo
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>