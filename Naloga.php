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

// Preveri parametre
$theme_id = $_GET['theme_id'] ?? null;
$subject_id = $_GET['subject_id'] ?? null;
$naloga_id = $_GET['naloga_id'] ?? null;

$success_message = "";
$error_message = "";

// PREVERI ALI NALOGA OBSTAJA
$assignment_exists = false;
$assignment_info = null;

if ($naloga_id) {
    $sql_check = "SELECT * FROM Naloga WHERE Id_naloge = ?";
    $stmt = $conn->prepare($sql_check);
    if ($stmt) {
        $stmt->bind_param("i", $naloga_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $assignment_exists = true;
            $assignment_info = $result->fetch_assoc();
        }
        $stmt->close();
    }
}

// UČITELJ - USTVARJANJE NALOGE (samo če naloga še ne obstaja)
if ($user_type === 'ucitelj' && !$assignment_exists && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_assignment'])) {
    $assignment_description = $_POST['assignment_description'] ?? '';
    $instructions = $_POST['instructions'] ?? '';
    
    if (empty($assignment_description)) {
        $error_message = "Naslov naloge je obvezen!";
    } elseif (!$theme_id) {
        $error_message = "Manjka tema (theme_id)!";
    } else {
        // Pridobi naslednji ID za nalogo
        $sql_id = "SELECT MAX(Id_naloge) as max_id FROM Naloga";
        $result = $conn->query($sql_id);
        $row = $result->fetch_assoc();
        $next_id = ($row['max_id'] ?? 0) + 1;
        
        // Vstavi podatke v bazo
        $sql = "INSERT INTO Naloga (Id_naloge, Id_vsebine, opis_naloge, navodila) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("iiss", $next_id, $theme_id, $assignment_description, $instructions);
            
            if ($stmt->execute()) {
                $success_message = "Naloga je bila uspešno ustvarjena!";
                $assignment_exists = true;
                $naloga_id = $next_id;
                // Osveži podatke o nalogi
                $sql_check = "SELECT * FROM Naloga WHERE Id_naloge = ?";
                $stmt_check = $conn->prepare($sql_check);
                $stmt_check->bind_param("i", $naloga_id);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                $assignment_info = $result_check->fetch_assoc();
                $stmt_check->close();
            } else {
                $error_message = "Napaka pri shranjevanju v bazo: " . $stmt->error;
            }
            
            $stmt->close();
        } else {
            $error_message = "Napaka pri pripravi poizvedbe: " . $conn->error;
        }
    }
}

// UČENEC - ODAJA NALOGE
if ($user_type === 'ucenec' && $assignment_exists && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_assignment'])) {
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
        $new_file_name = $safe_file_name . '_' . $user_id . '_' . uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $new_file_name;
        
        // Premakni datoteko v ciljni imenik
        if (move_uploaded_file($file_tmp_name, $file_path)) {
            // Preveri, ali učenec že ima oddano nalogo
            $sql_check = "SELECT id_oddaje, datoteka FROM OddajaNaloge WHERE id_naloge = ? AND id_dijaka = ?";
            $stmt_check = $conn->prepare($sql_check);
            
            if ($stmt_check) {
                $stmt_check->bind_param("ii", $naloga_id, $user_id);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                $existing_submission = $result_check->fetch_assoc();
                $stmt_check->close();
                
                if ($existing_submission) {
                    // Posodobi obstoječo oddajo
                    $sql = "UPDATE OddajaNaloge SET datoteka = ? WHERE id_oddaje = ?";
                    $stmt = $conn->prepare($sql);
                    
                    if ($stmt) {
                        $stmt->bind_param("si", $file_path, $existing_submission['id_oddaje']);
                        
                        // Izbriši staro datoteko
                        if (!empty($existing_submission['datoteka']) && file_exists($existing_submission['datoteka'])) {
                            unlink($existing_submission['datoteka']);
                        }
                    }
                } else {
                    // Ustvari novo oddajo
                    $sql = "INSERT INTO OddajaNaloge (id_naloge, id_dijaka, datoteka) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    
                    if ($stmt) {
                        $stmt->bind_param("iis", $naloga_id, $user_id, $file_path);
                    }
                }
                
                if ($stmt && $stmt->execute()) {
                    $success_message = "Naloga je bila uspešno oddana!";
                } else {
                    $error_message = "Napaka pri shranjevanju v bazo: " . $stmt->error;
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
                
                if ($stmt) {
                    $stmt->close();
                }
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

// UČENEC - izbris oddane datoteke
if ($user_type === 'ucenec' && $assignment_exists && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_assignment'])) {
    // Pridobi pot do datoteke
    $sql_file = "SELECT id_oddaje, datoteka FROM OddajaNaloge WHERE id_naloge = ? AND id_dijaka = ?";
    $stmt = $conn->prepare($sql_file);
    
    if ($stmt) {
        $stmt->bind_param("ii", $naloga_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $submission = $result->fetch_assoc();
        $stmt->close();
        
        if ($submission && !empty($submission['datoteka'])) {
            $file_path = $submission['datoteka'];
            
            // Izbriši datoteko s strežnika
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Posodobi bazo - nastavi datoteko na NULL
            $sql_update = "UPDATE OddajaNaloge SET datoteka = NULL WHERE id_oddaje = ?";
            $stmt_update = $conn->prepare($sql_update);
            
            if ($stmt_update) {
                $stmt_update->bind_param("i", $submission['id_oddaje']);
                
                if ($stmt_update->execute()) {
                    $success_message = "Oddana datoteka je bila uspešno izbrisana!";
                } else {
                    $error_message = "Napaka pri posodabljanju baze: " . $stmt_update->error;
                }
                
                $stmt_update->close();
            } else {
                $error_message = "Napaka pri pripravi poizvedbe: " . $conn->error;
            }
        } else {
            $error_message = "Datoteka ne obstaja ali je že izbrisana";
        }
    } else {
        $error_message = "Napaka pri pripravi poizvedbe: " . $conn->error;
    }
}

// UČITELJ - DODAJANJE/UREDJANJE KOMENTARJA
if ($user_type === 'ucitelj' && $assignment_exists && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_comment'])) {
    $comment = $_POST['comment'] ?? '';
    $student_id = $_POST['student_id'] ?? '';
    
    if (empty($student_id)) {
        $error_message = "Manjka ID učenca!";
    } else {
        // Preveri, ali oddaja obstaja
        $sql_check = "SELECT id_oddaje FROM OddajaNaloge WHERE id_naloge = ? AND id_dijaka = ?";
        $stmt_check = $conn->prepare($sql_check);
        
        if ($stmt_check) {
            $stmt_check->bind_param("ii", $naloga_id, $student_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $existing_submission = $result_check->fetch_assoc();
            $stmt_check->close();
            
            if ($existing_submission) {
                // Posodobi komentar
                $sql = "UPDATE OddajaNaloge SET komentar = ? WHERE id_oddaje = ?";
                $stmt = $conn->prepare($sql);
                
                if ($stmt) {
                    $stmt->bind_param("si", $comment, $existing_submission['id_oddaje']);
                    
                    if ($stmt->execute()) {
                        $success_message = "Komentar je bil uspešno shranjen!";
                    } else {
                        $error_message = "Napaka pri shranjevanju komentarja: " . $stmt->error;
                    }
                    
                    $stmt->close();
                } else {
                    $error_message = "Napaka pri pripravi poizvedbe: " . $conn->error;
                }
            } else {
                $error_message = "Oddaja ne obstaja!";
            }
        } else {
            $error_message = "Napaka pri pripravi poizvedbe: " . $conn->error;
        }
    }
}

// Pridobi podatke za prikaz
$user_submission = null;
$submitted_assignments = [];

// Za učence: pridobi oddajo tega učenca
if ($user_type === 'ucenec' && $assignment_exists) {
    $sql_submission = "SELECT datoteka, komentar FROM OddajaNaloge WHERE id_naloge = ? AND id_dijaka = ?";
    $stmt = $conn->prepare($sql_submission);
    if ($stmt) {
        $stmt->bind_param("ii", $naloga_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_submission = $result->fetch_assoc();
        $stmt->close();
    }
}

// Za učitelje: pridobi seznam vseh oddanih nalog
if ($user_type === 'ucitelj' && $assignment_exists) {
    $sql_submitted = "SELECT onl.id_oddaje, onl.datoteka, onl.datum_oddaje, onl.komentar,
                             u.ime, u.priimek, u.id_dijaka as id_dijaka
                      FROM OddajaNaloge onl
                      INNER JOIN Ucenec u ON onl.id_dijaka = u.id_dijaka
                      WHERE onl.id_naloge = ? AND onl.datoteka IS NOT NULL
                      ORDER BY onl.datum_oddaje DESC";
    $stmt = $conn->prepare($sql_submitted);
    if ($stmt) {
        $stmt->bind_param("i", $naloga_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $submitted_assignments[] = $row;
        }
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
    <title><?php echo $user_type === 'ucitelj' ? ($assignment_exists ? 'Pregled Nalog' : 'Dodaj Nalogo') : 'Oddaja Naloge'; ?></title>
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
        .top-bar {
            display: flex;
            justify-content: end;
            align-items: center;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            background-color: white;
        }


        .top-bar h1 {
            color: var(--dark);
            margin: 0;
        }

        .user-icon {
            font-size: 24px;
            color: var(--primary);
            text-decoration: none;
            transition: all 0.3s;
        }

        .user-icon:hover {
            transform: scale(1.1);
            color: var(--secondary);
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
        
        .back-btn {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-btn:hover {
            color: white;
            transform: translateY(-2px);
        }

        .history-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
            padding: 8px 15px;
            border: 2px solid var(--primary);
            border-radius: 20px;
            transition: all 0.3s;
        }

        .history-back-btn:hover {
            background: var(--primary);
            color: white;
            text-decoration: none;
        }

        .comment-btn {
            background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
            border: none;
            border-radius: 6px;
            padding: 8px 15px;
            color: white;
            font-size: 0.9rem;
        }
        
        .comment-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(255, 193, 7, 0.3);
        }
        
        .alert {
            margin-bottom: 20px;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(106, 17, 203, 0.25);
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
        
        .delete-btn {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            border-radius: 6px;
            padding: 8px 15px;
            color: white;
            font-size: 0.9rem;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .submission-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
        }
        
        .comment-section {
            background-color: #f8f9fa;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-top: 15px;
            border-radius: 0 8px 8px 0;
        }
        
        .comment-form {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="floating-elements" id="floatingElements"></div>
    
    <div class="top-bar">     
        <a href="profile.php" class="user-icon">
            <i class="fas fa-user"></i>
        </a>
    </div>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="glass-card mb-4">
                    <div class="header-card">
                        <h1 class="h3 fw-bold mb-2">
                            <?php 
                            if ($user_type === 'ucitelj') {
                                echo $assignment_exists ? 'Pregled Oddanih Nalog' : 'Ustvari Novo Nalogo';
                            } else {
                                echo 'Oddaja Naloge';
                            }
                            ?>
                        </h1>
                        <p class="mb-0">
                            <?php echo $user_type === 'ucitelj' ? 'Učiteljski portal' : 'Učenska aplikacija'; ?>
                        </p>
                    </div>
                    
                    <div class="assignment-card">
                        <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                        </div>
                        <?php endif; ?>

                        <!-- UČITELJ - PREGLED ALI USTVARJANJE -->
                        <?php if ($user_type === 'ucitelj'): ?>
                        
                        <?php if ($assignment_exists): ?>
                            <!-- PRIKAŽI ODDANE NALOGE -->
                            <?php if (isset($assignment_info)): ?>
                            <div class="mb-4 p-3 bg-light rounded">
                                <h5 class="fw-bold"><?php echo htmlspecialchars($assignment_info['opis_naloge']); ?></h5>
                                <div class="mb-2">
                                    <strong>Navodila:</strong>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($assignment_info['navodila'])); ?></p>
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
                                <div class="submission-item">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($submitted['ime'] . ' ' . $submitted['priimek']); ?></h6>
                                        <span class="badge bg-success">
                                            Oddano: <?php echo date('d.m.Y H:i', strtotime($submitted['datum_oddaje'])); ?>
                                        </span>
                                    </div>
                                    
                                    <p class="mb-2"><strong>Datoteka:</strong> <?php echo htmlspecialchars(basename($submitted['datoteka'])); ?></p>
                                    
                                    <!-- Prikaži komentar, če obstaja -->
                                    <?php if (!empty($submitted['komentar'])): ?>
                                    <div class="comment-section">
                                        <strong><i class="fas fa-comment me-2 text-warning"></i>Komentar:</strong>
                                        <p class="mb-0 mt-1"><?php echo nl2br(htmlspecialchars($submitted['komentar'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-3 btn-group">
                                        <a href="<?php echo htmlspecialchars($submitted['datoteka']); ?>" 
                                           class="btn download-btn" 
                                           download>
                                            <i class="fas fa-download me-2"></i>Prenesi nalogo
                                        </a>
                                        
                                        <!-- Gumb za komentiranje -->
                                        <button type="button" class="btn comment-btn" data-bs-toggle="modal" data-bs-target="#commentModal<?php echo $submitted['id_dijaka']; ?>">
                                            <i class="fas fa-comment me-2"></i>
                                            <?php echo empty($submitted['komentar']) ? 'Dodaj komentar' : 'Uredi komentar'; ?>
                                        </button>
                                    </div>
                                    
                                    <!-- Modal za komentiranje -->
                                    <div class="modal fade" id="commentModal<?php echo $submitted['id_dijaka']; ?>" tabindex="-1" aria-labelledby="commentModalLabel<?php echo $submitted['id_dijaka']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="commentModalLabel<?php echo $submitted['id_dijaka']; ?>">
                                                        Komentar za <?php echo htmlspecialchars($submitted['ime'] . ' ' . $submitted['priimek']); ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] . '?naloga_id=' . $naloga_id . '&theme_id=' . $theme_id . '&subject_id=' . $subject_id; ?>">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="comment<?php echo $submitted['id_dijaka']; ?>" class="form-label">Komentar:</label>
                                                            <textarea class="form-control" id="comment<?php echo $submitted['id_dijaka']; ?>" name="comment" rows="5" placeholder="Vnesite komentar za učenca..."><?php echo htmlspecialchars($submitted['komentar'] ?? ''); ?></textarea>
                                                        </div>
                                                        <input type="hidden" name="student_id" value="<?php echo $submitted['id_dijaka']; ?>">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Prekliči</button>
                                                        <button type="submit" name="add_comment" class="btn comment-btn">
                                                            <i class="fas fa-save me-2"></i>Shrani komentar
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- DODAN GUMB NAZAJ ZA UČITELJE -->
                            <div class="mt-4 text-center">
                                <button onclick="window.history.back()" class="btn history-back-btn">
                                    <i class="fas fa-arrow-left me-2"></i>Nazaj na prejšnjo stran
                                </button>
                            </div>

                        <?php else: ?>
                            <!-- PRIKAŽI OBLIKO ZA USTVARJANJE NALOGE -->
                            <?php if (!$theme_id): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Manjka parameter teme. Vrnite se na seznam snovi in znova izberite "Dodaj nalogo".
                                </div>
                                <div class="text-center mt-4">
                                    <a href="predmet.php?subject_id=<?php echo $subject_id; ?>" class="btn back-btn">
                                        <i class="fas fa-arrow-left me-2"></i>Nazaj na snovi
                                    </a>
                                </div>
                            <?php else: ?>
                                <form action="<?php echo $_SERVER['PHP_SELF'] . '?theme_id=' . $theme_id . '&subject_id=' . $subject_id; ?>" method="POST">
                                    <div class="mb-3">
                                        <label for="assignment_description" class="form-label fw-bold">Naslov naloge:</label>
                                        <input type="text" class="form-control" name="assignment_description" id="assignment_description" 
                                               placeholder="Vnesite naslov naloge (npr. 'Domaca naloga iz matematike')" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="instructions" class="form-label fw-bold">Navodila:</label>
                                        <textarea class="form-control" name="instructions" id="instructions" rows="6" 
                                                  placeholder="Podrobna navodila za nalogo..."></textarea>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <button onclick="window.history.back()" class="btn history-back-btn">
                                        <i class="fas fa-arrow-left me-2"></i>Nazaj na prejšnjo stran
                                    </button>
                                        
                                        <button type="submit" name="create_assignment" class="btn submit-btn">
                                            <i class="fas fa-plus me-2"></i>USTVARI NALOGO
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- UČENEC - ODAJA NALOGE -->
                        <?php else: ?>
                        <?php if (!$assignment_exists): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                                <h5 class="text-warning">Naloga ne obstaja</h5>
                                <p class="text-muted">Naloga z ID <?php echo htmlspecialchars($naloga_id); ?> ni bila najdena.</p>
                                <a href="predmet.php?subject_id=<?php echo $subject_id; ?>" class="btn back-btn">
                                    <i class="fas fa-arrow-left me-2"></i>Nazaj na snovi
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="assignment-item p-3 border rounded">
                                <h5 class="fw-bold"><?php echo htmlspecialchars($assignment_info['opis_naloge']); ?></h5>
                                <div class="mb-3">
                                    <strong>Navodila:</strong>
                                    <p><?php echo nl2br(htmlspecialchars($assignment_info['navodila'])); ?></p>
                                </div>
                                
                                <?php if (empty($user_submission) || empty($user_submission['datoteka'])): ?>
                                <form action="<?php echo $_SERVER['PHP_SELF'] . '?naloga_id=' . $naloga_id . '&theme_id=' . $theme_id . '&subject_id=' . $subject_id; ?>" method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="assignment_file" class="form-label fw-bold">Izberi datoteko:</label>
                                        <input type="file" class="form-control" name="assignment_file" id="assignment_file" required>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <button onclick="window.history.back()" class="btn history-back-btn">
                                            <i class="fas fa-arrow-left me-2"></i>Nazaj
                                        </button>
                                        
                                        <button type="submit" name="submit_assignment" class="btn submit-btn">
                                            <i class="fas fa-paper-plane me-2"></i>ODDAJ NALOGO
                                        </button>
                                    </div>
                                </form>
                                <?php else: ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Naloga je oddana</strong>
                                    <p class="mb-0">Datoteka: <?php echo htmlspecialchars(basename($user_submission['datoteka'])); ?></p>
                                    
                                    <!-- Prikaži komentar učitelja, če obstaja -->
                                    <?php if (!empty($user_submission['komentar'])): ?>
                                    <div class="comment-section mt-3">
                                        <strong><i class="fas fa-comment me-2 text-warning"></i>Komentar učitelja:</strong>
                                        <p class="mb-0 mt-1"><?php echo nl2br(htmlspecialchars($user_submission['komentar'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-2 btn-group">
                                        <a href="<?php echo htmlspecialchars($user_submission['datoteka']); ?>" 
                                           class="btn download-btn" 
                                           download>
                                            <i class="fas fa-download me-2"></i>Prenesi svojo nalogo
                                        </a>
                                        <form action="<?php echo $_SERVER['PHP_SELF'] . '?naloga_id=' . $naloga_id . '&theme_id=' . $theme_id . '&subject_id=' . $subject_id; ?>" method="POST" class="d-inline">
                                            <button type="submit" name="delete_assignment" class="btn delete-btn" onclick="return confirm('Ali ste prepričani, da želite izbrisati oddano datoteko?')">
                                                <i class="fas fa-trash-alt me-2"></i>Izbriši oddano datoteko
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- DODAN GUMB NAZAJ ZA UČENCE -->
                                <div class="mt-4 text-center">
                                    <button onclick="window.history.back()" class="btn history-back-btn">
                                        <i class="fas fa-arrow-left me-2"></i>Nazaj na prejšnjo stran
                                    </button>
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