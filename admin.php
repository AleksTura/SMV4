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

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: prijava.php');
    exit;
}

$success_message = "";
$error_message = "";

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    try {
        switch($action) {
            case 'delete_user':
                $id = $_POST['id'];
                $type = $_POST['type'];
                
                if ($type == 'ucitelj') {
                    // First delete from Uci_predmet and related records
                    $conn->query("DELETE FROM Uci_predmet WHERE Id_ucitelja = $id");
                    $conn->query("DELETE FROM Vsebina WHERE Id_ucitelja = $id");
                    $conn->query("DELETE FROM Dij_predmet WHERE Id_ucitelja = $id");
                    $conn->query("DELETE FROM Ucitelj WHERE Id_ucitelja = $id");
                } else {
                    // Delete student
                    $conn->query("DELETE FROM Dij_predmet WHERE Id_dijaka = $id");
                    $conn->query("DELETE FROM Ucenec WHERE Id_dijaka = $id");
                }
                $success_message = "Uporabnik uspešno izbrisan!";
                break;
                
            case 'delete_subject':
                $id = $_POST['id'];
                // Cascade delete will handle related records if foreign keys are set properly
                $conn->query("DELETE FROM Predmet WHERE Id_predmeta = $id");
                $success_message = "Predmet uspešno izbrisan!";
                break;
                
            case 'delete_theme':
                $id = $_POST['id'];
                $conn->query("DELETE FROM Naloga WHERE Id_vsebine = $id");
                $conn->query("DELETE FROM Vsebina WHERE Id_vsebine = $id");
                $success_message = "Snov uspešno izbrisana!";
                break;
                
            case 'delete_exercise':
                $id = $_POST['id'];
                $conn->query("DELETE FROM Naloga WHERE Id_naloge = $id");
                $success_message = "Naloga uspešno izbrisana!";
                break;
                
            case 'add_user':
                $ime = $_POST['ime'];
                $priimek = $_POST['priimek'];
                $geslo = password_hash($_POST['geslo'], PASSWORD_DEFAULT);
                $type = $_POST['type'];
                $letnik = $_POST['letnik'] ?? null;
                
                if ($type == 'ucitelj') {
                    $sql = "INSERT INTO Ucitelj (Ime, Priimek, Geslo) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sss", $ime, $priimek, $geslo);
                } else {
                    $sql = "INSERT INTO Ucenec (Ime, Priimek, Letnik, Geslo) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssss", $ime, $priimek, $letnik, $geslo);
                }
                $stmt->execute();
                $success_message = "Uporabnik uspešno dodan!";
                break;
                
            case 'add_subject':
                $ime_predmeta = $_POST['ime_predmeta'];
                $sql = "INSERT INTO Predmet (Ime_predmeta) VALUES (?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $ime_predmeta);
                $stmt->execute();
                $success_message = "Predmet uspešno dodan!";
                break;
        }
    } catch (Exception $e) {
        $error_message = "Napaka: " . $e->getMessage();
    }
}

// Get all data
$ucitelji = $conn->query("SELECT * FROM Ucitelj")->fetch_all(MYSQLI_ASSOC);
$ucenci = $conn->query("SELECT * FROM Ucenec")->fetch_all(MYSQLI_ASSOC);
$predmeti = $conn->query("SELECT * FROM Predmet")->fetch_all(MYSQLI_ASSOC);
$vsebine = $conn->query("SELECT v.*, p.Ime_predmeta, u.Ime as UciteljIme, u.Priimek as UciteljPriimek 
                         FROM Vsebina v 
                         JOIN Predmet p ON v.Id_predmeta = p.Id_predmeta 
                         JOIN Ucitelj u ON v.Id_ucitelja = u.Id_ucitelja")->fetch_all(MYSQLI_ASSOC);
$naloge = $conn->query("SELECT n.*, v.snov, p.Ime_predmeta 
                        FROM Naloga n 
                        JOIN Vsebina v ON n.Id_vsebine = v.Id_vsebine 
                        JOIN Predmet p ON v.Id_predmeta = p.Id_predmeta")->fetch_all(MYSQLI_ASSOC);
$uci_predmet = $conn->query("SELECT up.*, u.Ime as UciteljIme, u.Priimek as UciteljPriimek, p.Ime_predmeta 
                             FROM Uci_predmet up 
                             JOIN Ucitelj u ON up.Id_ucitelja = u.Id_ucitelja 
                             JOIN Predmet p ON up.Id_predmeta = p.Id_predmeta")->fetch_all(MYSQLI_ASSOC);
$dij_predmet = $conn->query("SELECT dp.*, u.Ime as UcenecIme, u.Priimek as UcenecPriimek, p.Ime_predmeta 
                             FROM Dij_predmet dp 
                             JOIN Ucenec u ON dp.Id_dijaka = u.Id_dijaka 
                             JOIN Predmet p ON dp.Id_predmeta = p.Id_predmeta")->fetch_all(MYSQLI_ASSOC);
?>

<html>
<head>
    <title>Admin Panel</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="izgled.css">
    <meta name="author" content="Špela Zeme">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-section {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: var(--shadow);
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .admin-table th,
        .admin-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .admin-table th {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            font-weight: 600;
        }
        
        .admin-table tr:hover {
            background: rgba(106, 17, 203, 0.05);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #ff8e8e 100%);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 6px 12px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, var(--warning) 0%, #ffe066 100%);
            color: var(--dark);
            border: none;
            border-radius: 5px;
            padding: 6px 12px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }
        
        .admin-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }
        
        .admin-form .form-group {
            margin-bottom: 0;
        }
        
        .form-section {
            grid-column: 1 / -1;
            background: rgba(106, 17, 203, 0.05);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        
        .section-title {
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 1.4em;
        }
    </style>
</head>
<body>
    <div class="floating-elements" id="floatingElements"></div>
    
    <div class="top-bar">
        <h1><i class="fas fa-cogs"></i> Admin Panel</h1>
        <a href="prvastran.php" class="user-icon">
            <i class="fas fa-home"></i> Domov
        </a>
    </div>
    
    <?php if ($success_message): ?>
        <div class="success" style="max-width: 1200px; margin: 0 auto 20px auto;">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="error" style="max-width: 1200px; margin: 0 auto 20px auto;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    
    <div style="max-width: 1200px; margin: 0 auto;">
        <!-- Add User Section -->
        <div class="admin-section">
            <h2 class="section-title"><i class="fas fa-user-plus"></i> Dodaj Uporabnika</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="add_user">
                <div class="form-group">
                    <label>Ime:</label>
                    <input type="text" name="ime" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Priimek:</label>
                    <input type="text" name="priimek" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Geslo:</label>
                    <input type="password" name="geslo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tip uporabnika:</label>
                    <select name="type" class="form-control" required onchange="toggleClassField(this)">
                        <option value="ucitelj">Učitelj</option>
                        <option value="ucenec">Učenec</option>
                    </select>
                </div>
                <div class="form-group" id="classField" style="display: none;">
                    <label>Letnik:</label>
                    <input type="text" name="letnik" class="form-control" placeholder="npr. 1A, 2B...">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <button type="submit" class="btn">
                        <i class="fas fa-plus"></i> Dodaj Uporabnika
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Add Subject Section -->
        <div class="admin-section">
            <h2 class="section-title"><i class="fas fa-book-medical"></i> Dodaj Predmet</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="add_subject">
                <div class="form-group">
                    <label>Ime predmeta:</label>
                    <input type="text" name="ime_predmeta" class="form-control" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn">
                        <i class="fas fa-plus"></i> Dodaj Predmet
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Users Management -->
        <div class="admin-section">
            <h2 class="section-title"><i class="fas fa-users"></i> Uporabniki</h2>
            
            <h3><i class="fas fa-chalkboard-teacher"></i> Učitelji</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ime</th>
                        <th>Priimek</th>
                        <th>Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($ucitelji as $ucitelj): ?>
                    <tr>
                        <td><?php echo $ucitelj['Id_ucitelja']; ?></td>
                        <td><?php echo htmlspecialchars($ucitelj['Ime']); ?></td>
                        <td><?php echo htmlspecialchars($ucitelj['Priimek']); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="id" value="<?php echo $ucitelj['Id_ucitelja']; ?>">
                                <input type="hidden" name="type" value="ucitelj">
                                <button type="submit" class="btn-danger" onclick="return confirm('Ste prepričani?')">
                                    <i class="fas fa-trash"></i> Izbriši
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <h3 style="margin-top: 30px;"><i class="fas fa-user-graduate"></i> Učenci</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ime</th>
                        <th>Priimek</th>
                        <th>Letnik</th>
                        <th>Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($ucenci as $ucenec): ?>
                    <tr>
                        <td><?php echo $ucenec['Id_dijaka']; ?></td>
                        <td><?php echo htmlspecialchars($ucenec['Ime']); ?></td>
                        <td><?php echo htmlspecialchars($ucenec['Priimek']); ?></td>
                        <td><?php echo htmlspecialchars($ucenec['Letnik']); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="id" value="<?php echo $ucenec['Id_dijaka']; ?>">
                                <input type="hidden" name="type" value="ucenec">
                                <button type="submit" class="btn-danger" onclick="return confirm('Ste prepričani?')">
                                    <i class="fas fa-trash"></i> Izbriši
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Subjects Management -->
        <div class="admin-section">
            <h2 class="section-title"><i class="fas fa-book"></i> Predmeti</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ime predmeta</th>
                        <th>Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($predmeti as $predmet): ?>
                    <tr>
                        <td><?php echo $predmet['Id_predmeta']; ?></td>
                        <td><?php echo htmlspecialchars($predmet['Ime_predmeta']); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete_subject">
                                <input type="hidden" name="id" value="<?php echo $predmet['Id_predmeta']; ?>">
                                <button type="submit" class="btn-danger" onclick="return confirm('Ste prepričani?')">
                                    <i class="fas fa-trash"></i> Izbriši
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Content Management -->
        <div class="admin-section">
            <h2 class="section-title"><i class="fas fa-folder"></i> Snovi</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Snov</th>
                        <th>Predmet</th>
                        <th>Učitelj</th>
                        <th>Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($vsebine as $vsebina): ?>
                    <tr>
                        <td><?php echo $vsebina['Id_vsebine']; ?></td>
                        <td><?php echo htmlspecialchars($vsebina['snov']); ?></td>
                        <td><?php echo htmlspecialchars($vsebina['Ime_predmeta']); ?></td>
                        <td><?php echo htmlspecialchars($vsebina['UciteljIme'] . ' ' . $vsebina['UciteljPriimek']); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete_theme">
                                <input type="hidden" name="id" value="<?php echo $vsebina['Id_vsebine']; ?>">
                                <button type="submit" class="btn-danger" onclick="return confirm('Ste prepričani?')">
                                    <i class="fas fa-trash"></i> Izbriši
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Exercises Management -->
        <div class="admin-section">
            <h2 class="section-title"><i class="fas fa-tasks"></i> Naloge</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Opis naloge</th>
                        <th>Snov</th>
                        <th>Predmet</th>
                        <th>Komentar</th>
                        <th>Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($naloge as $naloga): ?>
                    <tr>
                        <td><?php echo $naloga['Id_naloge']; ?></td>
                        <td><?php echo htmlspecialchars($naloga['opis_naloge']); ?></td>
                        <td><?php echo htmlspecialchars($naloga['snov']); ?></td>
                        <td><?php echo htmlspecialchars($naloga['Ime_predmeta']); ?></td>
                        <td><?php echo htmlspecialchars(substr($naloga['komentar'] ?? '', 0, 50)) . '...'; ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete_exercise">
                                <input type="hidden" name="id" value="<?php echo $naloga['Id_naloge']; ?>">
                                <button type="submit" class="btn-danger" onclick="return confirm('Ste prepričani?')">
                                    <i class="fas fa-trash"></i> Izbriši
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleClassField(select) {
            const classField = document.getElementById('classField');
            if (select.value === 'ucenec') {
                classField.style.display = 'block';
            } else {
                classField.style.display = 'none';
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>