<?php
// Database connection
$servername = "localhost"; // or your DB host
$username = "root";        // your DB username
$password = "";            // your DB password
$dbname = "my_database";   // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch users from the database
$sql = "SELECT Ime_predmeta 
        FROM Uci_predmet";
$result = $conn->query($sql);

?>
<html>
    <head>
        <title>Spletna učilnica</title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="izgled.css">
        <meta name="author" content="Špela Zeme">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    </head>

    <body>
        <div class="top-bar">
            <h1>Spletna učilnica</h1>
            <a href="profil.php"><i class="fa fa-user"></i></a>
        </div>
        <div class="list container">
            <ul>
                <?php
                // Check if there are results
                if ($result->num_rows > 0) {
                    // Output each row of data
                    while($row = $result->fetch_assoc()) {
                        echo "<li>" . htmlspecialchars($row['name']) . " (" . htmlspecialchars($row['email']) . ")</li>";
                    }
                } else {
                    echo "<li>No users found.</li>";
                }
                ?>
            </ul>
        </div>
    </body>
</html>
<?php
// Close the database connection
$conn->close();
?>
