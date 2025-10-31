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
            margin-top: 20px;
        }
        
        .back-btn:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
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
                <?php else: ?>
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Podatki o uporabniku niso bili najdeni.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>