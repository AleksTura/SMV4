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
        
        // Generiraj unikatno ime datoteke
        $new_file_name = uniqid() . '_' . $file_name;
        $file_path = $upload_dir . $new_file_name;
        
        // Premakni datoteko v ciljni imenik
        if (move_uploaded_file($file_tmp_name, $file_path)) {
            // Posodobi nalogo z datoteko
            $sql = "UPDATE Naloga SET datoteka = ? WHERE Id_naloge = ?";
            
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("si", $file_path, $assignment_id);
                
                if ($stmt->execute()) {
                    $submission_success = true;
                    $success_message = "Naloga je bila uspešno oddana!";
                } else {
                    $error_message = "Napaka pri shranjevanju v bazo: " . $stmt->error;
                    unlink($file_path);
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

// Pridobi seznam nalog za učenca
$assignments = [];
if ($user_type === 'ucenec') {
    $sql_assignments = "SELECT Id_naloge, opis_naloge, komentar, datoteka FROM Naloga";
    $result = $conn->query($sql_assignments);
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
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
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #6a11cb;
            --secondary: #2575fc;
            --success: #00b09b;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
        }
        
        .header-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }
        
        .assignment-card {
            padding: 30px;
        }
        
        .drop-area {
            border: 3px dashed var(--primary);
            border-radius: 15px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s ease;
            background: rgba(106, 17, 203, 0.05);
            margin: 25px 0;
            position: relative;
            overflow: hidden;
        }
        
        .drop-area::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.7s;
        }
        
        .drop-area:hover::before {
            left: 100%;
        }
        
        .drop-area:hover {
            background: rgba(106, 17, 203, 0.1);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .drop-area.dragover {
            background: rgba(106, 17, 203, 0.2);
            border-color: var(--secondary);
            transform: scale(1.02);
        }
        
        .drop-icon {
            font-size: 70px;
            color: var(--primary);
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .drop-area:hover .drop-icon {
            transform: scale(1.1);
        }
        
        .submit-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            border-radius: 50px;
            padding: 12px 40px;
            font-weight: 600;
            font-size: 18px;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.4);
            position: relative;
            overflow: hidden;
        }
        
        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.7s;
        }
        
        .submit-btn:hover::before {
            left: 100%;
        }
        
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 117, 252, 0.6);
        }
        
        .assignment-title {
            color: var(--dark);
            border-bottom: 2px solid #f1f1f1;
            padding-bottom: 15px;
            margin-bottom: 20px;
            position: relative;
        }
        
        .assignment-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 3px;
        }
        
        .instructions {
            background: rgba(106, 17, 203, 0.05);
            border-left: 4px solid var(--primary);
            padding: 20px;
            border-radius: 0 10px 10px 0;
            margin-top: 20px;
            transition: all 0.3s;
        }
        
        .instructions:hover {
            background: rgba(106, 17, 203, 0.1);
            transform: translateX(5px);
        }
        
        .file-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 15px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: none;
        }
        
        .progress-container {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            margin: 15px 0;
            overflow: hidden;
            display: none;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 4px;
            width: 0%;
            transition: width 0.4s ease;
        }
        
        .floating-elements {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        
        .floating-element {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 15s infinite linear;
        }
        
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-1000px) rotate(720deg); }
        }
        
        .file-list {
            margin-top: 20px;
            display: none;
        }
        
        .file-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background: white;
            border-radius: 10px;
            margin-bottom: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .file-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .file-icon {
            font-size: 24px;
            margin-right: 15px;
            color: var(--primary);
        }
        
        .alert-success {
            border-radius: 15px;
            border: none;
            background: rgba(0, 176, 155, 0.1);
            color: var(--success);
        }
    </style>
</head>
<body>
    <div class="floating-elements"></div>
    
    <div class="container main-container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="glass-card mb-4">
                    <!-- HEADER -->
                    <div class="header-card">
                        <h1 class="display-5 fw-bold mb-3 animate__animated animate__fadeInDown">
                            <?php echo $user_type === 'ucitelj' ? 'Ustvarjanje Naloge' : 'Oddaja Naloge'; ?>
                        </h1>
                        <p class="mb-0 fs-5 animate__animated animate__fadeInUp">
                            <?php echo $user_type === 'ucitelj' ? 'Učiteljski portal' : 'Učenska aplikacija'; ?>
                        </p>
                    </div>
                    
                    <div class="assignment-card">
                        <?php if ($submission_success): ?>
                        <div class="alert alert-success animate__animated animate__fadeIn">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                        </div>
                        <?php endif; ?>

                        <!-- UČITELJ - SESTAVLJANJE NALOGE -->
                        <?php if ($user_type === 'ucitelj'): ?>
                        <form action="oddaja_naloge.php" method="POST" id="assignmentForm">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="assignment_description" class="form-label fw-bold">Naslov naloge:</label>
                                        <input type="text" class="form-control" id="assignment_description" name="assignment_description" 
                                               placeholder="Vnesite naslov naloge" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="subject_content_id" class="form-label fw-bold">ID vsebine:</label>
                                        <input type="number" class="form-control" id="subject_content_id" name="subject_content_id" 
                                               placeholder="Vnesite ID vsebine" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-4">
                                <label for="instructions" class="form-label fw-bold">Navodila za nalogo:</label>
                                <textarea class="form-control" id="instructions" name="instructions" rows="6" 
                                          placeholder="Podrobna navodila za izvedbo naloge..." required></textarea>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" name="create_assignment" class="btn btn-primary submit-btn">
                                    <i class="fas fa-save me-2"></i>USTVARI NALOGO
                                </button>
                            </div>
                        </form>

                        <!-- UČENEC - ODAJA NALOGE -->
                        <?php else: ?>
                        <?php if (empty($assignments)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">Trenutno ni na voljo nobenih nalog</h4>
                            </div>
                        <?php else: ?>
                            <?php foreach ($assignments as $assignment): ?>
                            <div class="assignment-item mb-4 p-4 border rounded">
                                <h4 class="assignment-title"><?php echo htmlspecialchars($assignment['opis_naloge']); ?></h4>
                                <div class="instructions mb-3">
                                    <h6><i class="fas fa-info-circle me-2"></i>Navodila:</h6>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($assignment['komentar'])); ?></p>
                                </div>
                                
                                <?php if (empty($assignment['datoteka'])): ?>
                                <form action="oddaja_naloge.php" method="POST" enctype="multipart/form-data" class="assignment-form">
                                    <input type="hidden" name="assignment_id" value="<?php echo $assignment['Id_naloge']; ?>">
                                    
                                    <div class="drop-area" id="DropFile_<?php echo $assignment['Id_naloge']; ?>">
                                        <div class="drop-icon">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                        </div>
                                        <h4>Povlecite datoteko sem</h4>
                                        <p class="text-muted">ali kliknite za izbiro datoteke</p>
                                        <div class="mt-3">
                                            <button type="button" class="btn btn-outline-primary fileSelectBtn">
                                                <i class="fas fa-folder-open me-2"></i>Izberi datoteko
                                            </button>
                                        </div>
                                        <input type="file" name="assignment_file" class="d-none fileInput" required>
                                    </div>
                                    
                                    <div class="file-list" id="fileList_<?php echo $assignment['Id_naloge']; ?>"></div>
                                    
                                    <div class="text-center mt-3">
                                        <button type="submit" name="submit_assignment" class="btn btn-primary submit-btn">
                                            <i class="fas fa-paper-plane me-2"></i>ODDAJ NALOGO
                                        </button>
                                    </div>
                                </form>
                                <?php else: ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Naloga je oddana</strong>
                                    <p class="mb-0 mt-1">Datoteka: <?php echo basename($assignment['datoteka']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="text-center text-white">
                    <p>Spletna aplikacija za oddajo nalog &copy; 2023</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            createFloatingElements();
            
            // File selection for students
            document.querySelectorAll('.fileSelectBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const form = this.closest('.assignment-form');
                    form.querySelector('.fileInput').click();
                });
            });
            
            // File input change
            document.querySelectorAll('.fileInput').forEach(input => {
                input.addEventListener('change', handleFileSelect);
            });
            
            <?php if ($error_message): ?>
            alert('<?php echo $error_message; ?>');
            <?php endif; ?>
        });

        // Ostale JavaScript funkcije ostanejo enake...
    </script>
</body>
</html>