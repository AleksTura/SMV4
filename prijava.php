<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="izgled.css">
    <title>Prijava</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="floating-elements" id="floatingElements"></div>
    
    <div class="glass-card">
        <div class="header-card">
            <h1>Prijava</h1>
        </div>
        
        <div class="VpisBox">
            <form action="prijava.php" method="POST">
                <div class="form-group">
                    <label for="username">Uporabniško ime</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Uporabniško ime" required>
                </div>
               
                <div class="form-group">
                    <label for="password">Geslo</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Geslo" required>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt me-2"></i>Prijava
                </button>
                
                <a href="#" class="link">Pozabljeno geslo?</a>
                <a href="registracija.php" class="link">Še nimaš računa? Registriraj se</a>
            </form>
        </div>
    </div>

    <script>
        // Create floating elements
        function createFloatingElements() {
            const container = document.getElementById('floatingElements');
            const colors = ['rgba(106, 17, 203, 0.3)', 'rgba(37, 117, 252, 0.3)', 'rgba(255, 255, 255, 0.2)'];
            
            for (let i = 0; i < 15; i++) {
                const element = document.createElement('div');
                element.classList.add('floating-element');
                
                // Random properties
                const size = Math.random() * 60 + 20;
                const left = Math.random() * 100;
                const animationDuration = Math.random() * 30 + 20;
                const animationDelay = Math.random() * 5;
                const color = colors[Math.floor(Math.random() * colors.length)];
                
                element.style.width = `${size}px`;
                element.style.height = `${size}px`;
                element.style.left = `${left}%`;
                element.style.animationDuration = `${animationDuration}s`;
                element.style.animationDelay = `${animationDelay}s`;
                element.style.background = color;
                
                container.appendChild(element);
            }
        }
        
        // Initialize on page load
        window.onload = createFloatingElements;
    </script>
</body>
</html>
