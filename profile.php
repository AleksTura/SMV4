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

$success_message = "";
$error_message = "";

// Obdelaj prijavo na predmet
if ($user_type === 'ucenec' && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enroll_subject'])) {
    $selected_subject = $_POST['subject'] ?? '';
    
    if (empty($selected_subject)) {
        $error_message = "Izberite predmet!";
    } else {
        // Razčleni izbrani predmet (format: "id_ucitelja|id_predmeta")
        list($id_ucitelja, $id_predmeta) = explode('|', $selected_subject);
        
        // Preveri, ali je učenec že prijavljen na ta predmet
        $check_sql = "SELECT * FROM Dij_predmet WHERE Id_dijaka = ? AND Id_ucitelja = ? AND Id_predmeta = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("iii", $user_id, $id_ucitelja, $id_predmeta);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Na ta predmet ste že prijavljeni!";
        } else {
            // Vstavi novo prijavo
            $insert_sql = "INSERT INTO Dij_predmet (Id_dijaka, Id_ucitelja, Id_predmeta) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("iii", $user_id, $id_ucitelja, $id_predmeta);
            
            if ($stmt->execute()) {
                $success_message = "Uspešno ste se prijavili na predmet!";
            } else {
                $error_message = "Napaka pri prijavi na predmet: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

// Pridobi podatke o uporabniku
$user_data = null;
if ($user_type === 'ucitelj') {
    $sql = "SELECT ime, priimek FROM Ucitelj WHERE id_ucitelja = ?";
} else {
    $sql = "SELECT ime, priimek, letnik FROM Ucenec WHERE id_dijaka = ?";
}

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $stmt->close();
}

// Pridobi seznam vseh predmetov z učitelji za prijavo
$available_subjects = [];
if ($user_type === 'ucenec') {
    $subjects_sql = "SELECT p.Id_predmeta, p.Ime_predmeta, u.Id_ucitelja, u.ime, u.priimek 
                     FROM Predmet p 
                     JOIN Uci_predmet up ON p.Id_predmeta = up.Id_predmeta 
                     JOIN Ucitelj u ON up.Id_ucitelja = u.Id_ucitelja 
                     ORDER BY p.Ime_predmeta, u.priimek, u.ime";
    $result = $conn->query($subjects_sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $available_subjects[] = $row;
        }
    }
    
    // Pridobi že prijavljene predmete
    $enrolled_subjects = [];
    $enrolled_sql = "SELECT p.Id_predmeta, p.Ime_predmeta, u.ime, u.priimek 
                     FROM Dij_predmet dp 
                     JOIN Predmet p ON dp.Id_predmeta = p.Id_predmeta 
                     JOIN Ucitelj u ON dp.Id_ucitelja = u.Id_ucitelja 
                     WHERE dp.Id_dijaka = ? 
                     ORDER BY p.Ime_predmeta";
    $stmt = $conn->prepare($enrolled_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $enrolled_subjects[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moj Profil</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a11cb;
            --secondary: #2575fc;
            --success: #28a745;
            --warning: #ffc107;
            --info: #17a2b8;
            --light-bg: #f8f9fa;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }

        .top-bar h1 {
            color: #333;
            margin: 0;
            font-weight: 700;
        }

        .nav-icon {
            font-size: 18px;
            color: var(--primary);
            text-decoration: none;
            transition: all 0.3s;
            padding: 8px 15px;
            border-radius: 8px;
        }

        .nav-icon:hover {
            background-color: rgba(106, 17, 203, 0.1);
            transform: translateY(-2px);
            color: var(--secondary);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .header-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .header-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
        }
        
        .profile-card {
            padding: 40px;
        }
        
        .user-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            box-shadow: 0 10px 25px rgba(106, 17, 203, 0.3);
            border: 5px solid white;
        }
        
        .user-avatar i {
            font-size: 50px;
            color: white;
        }
        
        .user-name {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .user-role {
            display: inline-block;
            padding: 6px 20px;
            border-radius: 20px;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .role-teacher {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }
        
        .role-student {
            background: linear-gradient(135deg, var(--success) 0%, #20c997 100%);
            color: white;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .info-card {
            background: var(--light-bg);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s;
            border-left: 4px solid var(--primary);
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .info-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            margin-bottom: 8px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #333;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .password-btn {
            background: linear-gradient(135deg, var(--warning) 0%, #ffb300 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .password-btn:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
        }
        
        .enroll-btn {
            background: linear-gradient(135deg, var(--info) 0%, #138496 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .enroll-btn:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(23, 162, 184, 0.4);
        }
        
        .back-btn {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
        }
        
        .subject-section {
            margin-top: 40px;
            padding: 25px;
            background: var(--light-bg);
            border-radius: 15px;
            border-left: 4px solid var(--info);
        }
        
        .subject-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .subject-item {
            background: white;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid var(--success);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .subject-name {
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        
        .teacher-name {
            color: #666;
            font-size: 0.9rem;
        }
        
        .no-subjects {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .top-bar {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .profile-card {
                padding: 25px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .action-buttons a {
                width: 100%;
                max-width: 250px;
                justify-content: center;
            }
            
            .subject-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <h1>Moj Profil</h1>
        <div>
            <a href="prvastran.php" class="nav-icon me-2">
                <i class="fas fa-home me-2"></i> Domov
            </a>
            <a href="prijava.php" class="nav-icon" onclick="sessionStorage.clear()">
                <i class="fas fa-sign-out-alt me-2"></i> Odjavi se
            </a>
        </div>
    </div>
    
    <div class="container">
        <div class="glass-card">
            <div class="header-card">
                <h1 class="h2 fw-bold mb-2">Osebni Podatki</h1>
                <p class="mb-0">Pregled vaših podatkov in informacij</p>
            </div>
            
            <div class="profile-card">
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

                <?php if ($user_data): ?>
                    <div class="user-avatar">
                        <?php if ($user_type === 'ucitelj'): ?>
                            <i class="fas fa-chalkboard-teacher"></i>
                        <?php else: ?>
                            <i class="fas fa-user-graduate"></i>
                        <?php endif; ?>
                    </div>
                    
                    <h2 class="user-name"><?php echo htmlspecialchars($user_data['ime'] . ' ' . $user_data['priimek']); ?></h2>
                    
                    <div class="info-grid">
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="info-label">Ime in priimek</div>
                            <div class="info-value"><?php echo htmlspecialchars($user_data['ime'] . ' ' . $user_data['priimek']); ?></div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-icon">
                                <?php if ($user_type === 'ucitelj'): ?>
                                    <i class="fas fa-chalkboard-teacher"></i>
                                <?php else: ?>
                                    <i class="fas fa-user-graduate"></i>
                                <?php endif; ?>
                            </div>
                            <div class="info-label">Vloga</div>
                            <div class="info-value">
                                <?php echo $user_type === 'ucitelj' ? 'Učitelj' : 'Učenec'; ?>
                            </div>
                        </div>
                        
                        <?php if ($user_type === 'ucenec' && isset($user_data['letnik'])): ?>
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="info-label">Letnik</div>
                                <div class="info-value"><?php echo htmlspecialchars($user_data['letnik']); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- DODANI GUMBI ZA AKCIJE -->
                    <div class="action-buttons">
                        <a href="prvastran.php" class="back-btn">
                            <i class="fas fa-arrow-left me-2"></i>Nazaj na domačo stran
                        </a>
                        <a href="pozabljeno_geslo.php" class="password-btn">
                            <i class="fas fa-key me-2"></i>Spremeni geslo
                        </a>
                        
                        <?php if ($user_type === 'ucenec'): ?>
                            <button type="button" class="enroll-btn" data-bs-toggle="modal" data-bs-target="#enrollModal">
                                <i class="fas fa-book me-2"></i>Prijavi se na predmet
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- PRIKAŽI PRIJAVLJENE PREDMETE ZA UČENCE -->
                    <?php if ($user_type === 'ucenec'): ?>
                    <div class="subject-section">
                        <h4 class="fw-bold mb-3">
                            <i class="fas fa-list-check me-2"></i>Moji predmeti
                        </h4>
                        
                        <?php if (!empty($enrolled_subjects)): ?>
                            <div class="subject-list">
                                <?php foreach ($enrolled_subjects as $subject): ?>
                                <div class="subject-item">
                                    <div class="subject-name"><?php echo htmlspecialchars($subject['Ime_predmeta']); ?></div>
                                    <div class="teacher-name">
                                        <i class="fas fa-chalkboard-teacher me-1"></i>
                                        <?php echo htmlspecialchars($subject['ime'] . ' ' . $subject['priimek']); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-subjects">
                                <i class="fas fa-book-open me-2"></i>
                                Še niste prijavljeni na noben predmet.
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Podatki o uporabniku niso bili najdeni.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal za prijavo na predmet -->
    <?php if ($user_type === 'ucenec'): ?>
    <div class="modal fade" id="enrollModal" tabindex="-1" aria-labelledby="enrollModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="enrollModalLabel">
                        <i class="fas fa-book me-2"></i>Prijava na predmet
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="subject" class="form-label fw-bold">Izberite predmet:</label>
                            <select class="form-control" id="subject" name="subject" required>
                                <option value="">-- Izberite predmet --</option>
                                <?php foreach ($available_subjects as $subject): ?>
                                    <option value="<?php echo $subject['Id_ucitelja'] . '|' . $subject['Id_predmeta']; ?>">
                                        <?php echo htmlspecialchars($subject['Ime_predmeta'] . ' - ' . $subject['ime'] . ' ' . $subject['priimek']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Prekliči</button>
                        <button type="submit" name="enroll_subject" class="btn enroll-btn">
                            <i class="fas fa-check me-2"></i>Prijavi se
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>