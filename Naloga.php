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
        
        .submitted-file {
            display: none;
            border: 2px solid var(--success);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            background: rgba(0, 176, 155, 0.05);
            margin: 25px 0;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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
        
        .file-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-top: 10px;
        }
        
        .file-info i {
            font-size: 50px;
            color: var(--success);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
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
        
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background: var(--primary);
            opacity: 0;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="floating-elements">
        <!-- Floating elements will be generated by JavaScript -->
    </div>
    
    <div class="container main-container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header Section -->
                <div class="glass-card mb-4">
                    <div class="header-card">
                        <h1 class="display-5 fw-bold mb-3 animate__animated animate__fadeInDown">Ime predmeta iz PB</h1>
                        <p class="mb-0 fs-5 animate__animated animate__fadeInUp">Spletna učilnica - Oddaja nalog</p>
                    </div>
                    
                    <!-- Assignment Section -->
                    <div class="assignment-card">
                        <h2 class="assignment-title">IME NALOGE</h2>
                        <p class="lead">Opis naloge</p>
                        
                        <!-- Instructions -->
                        <div class="instructions">
                            <h5><i class="fas fa-info-circle me-2"></i>Navodila za oddajo:</h5>
                            <ul class="mb-0">
                                <li>Pripravite datoteko v ustreznem formatu (PDF, DOC, JPG, PNG, ZIP)</li>
                                <li>Povlecite datoteko v spodnje območje ali kliknite za izbiro</li>
                                <li>Po preverjanju pritisnite gumb "ODDAJ"</li>
                            </ul>
                        </div>
                        
                        <!-- Drop Area -->
                        <div class="drop-area" id="DropFile">
                            <div class="drop-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <h4>Povlecite datoteko sem</h4>
                            <p class="text-muted">ali kliknite za izbiro datoteke</p>
                            <div class="mt-3">
                                <button class="btn btn-outline-primary" id="fileSelectBtn">
                                    <i class="fas fa-folder-open me-2"></i>Izberi datoteko
                                </button>
                            </div>
                            <input type="file" id="fileInput" class="d-none">
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="progress-container" id="progressContainer">
                            <div class="progress-bar" id="progressBar"></div>
                        </div>
                        
                        <!-- File Preview -->
                        <div id="filePreview" class="text-center"></div>
                        
                        <!-- File List -->
                        <div class="file-list" id="fileList"></div>
                        
                        <!-- Submitted File -->
                        <div class="submitted-file" id="DroppedFile">
                            <div class="file-info">
                                <i class="fas fa-check-circle"></i>
                                <div class="text-start">
                                    <h4 class="text-success mb-1">NALOGA ODDANA</h4>
                                    <p class="mb-0 fw-bold" id="submittedFileName">naloga.pdf</p>
                                    <small class="text-muted" id="submissionDate">Oddano: 25. maj 2023, 14:30</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="text-center mt-4">
                            <button id="OddajButton" class="btn btn-primary submit-btn" onclick="hide()">
                                <i class="fas fa-paper-plane me-2"></i>ODDAJ
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="text-center text-white">
                    <p>Spletna aplikacija za oddajo nalog &copy; 2023</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Your Original JavaScript + Enhanced Features -->
    <script>
        // Your original JavaScript code
        var DroppedFile = document.getElementById('DroppedFile');
        var DropFileBox = document.getElementById('DropFile');
        var OddajButton = document.getElementById('OddajButton');
        
        function hide() {
            if(DroppedFile.style.display == "none"){
                DropFileBox.style.display = 'none';
                DroppedFile.style.display = 'block';
                OddajButton.textContent = 'SPREMENI';
                OddajButton.innerHTML = '<i class="fas fa-edit me-2"></i>SPREMENI';
                
                // Add celebration effect
                createConfetti();
            }
            else{
                DropFileBox.style.display = 'flex';
                DroppedFile.style.display = 'none';
                OddajButton.textContent = 'ODDAJ';
                OddajButton.innerHTML = '<i class="fas fa-paper-plane me-2"></i>ODDAJ';
            }
        }
        
        // Enhanced functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Create floating elements
            createFloatingElements();
            
            // File selection button
            document.getElementById('fileSelectBtn').addEventListener('click', function() {
                document.getElementById('fileInput').click();
            });
            
            // File input change
            document.getElementById('fileInput').addEventListener('change', handleFileSelect);
            
            // Drag and drop functionality
            setupDragAndDrop();
        });
        
        function createFloatingElements() {
            const container = document.querySelector('.floating-elements');
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
        
        function setupDragAndDrop() {
            const dropArea = document.getElementById('DropFile');
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                dropArea.classList.add('dragover');
            }
            
            function unhighlight() {
                dropArea.classList.remove('dragover');
            }
            
            dropArea.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (files.length) {
                    handleFiles(files);
                }
            }
        }
        
        function handleFileSelect(e) {
            const files = e.target.files;
            handleFiles(files);
        }
        
        function handleFiles(files) {
            const fileList = document.getElementById('fileList');
            fileList.innerHTML = '';
            fileList.style.display = 'block';
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                displayFileInfo(file);
            }
            
            // Show progress bar and simulate upload
            const progressContainer = document.getElementById('progressContainer');
            const progressBar = document.getElementById('progressBar');
            
            progressContainer.style.display = 'block';
            progressBar.style.width = '0%';
            
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 10;
                if (progress >= 100) {
                    progress = 100;
                    clearInterval(interval);
                }
                progressBar.style.width = `${progress}%`;
            }, 200);
        }
        
        function displayFileInfo(file) {
            const fileList = document.getElementById('fileList');
            
            const fileItem = document.createElement('div');
            fileItem.classList.add('file-item');
            
            const fileIcon = getFileIcon(file.type);
            
            fileItem.innerHTML = `
                <div class="file-icon">
                    <i class="${fileIcon}"></i>
                </div>
                <div class="file-details">
                    <h6 class="mb-1">${file.name}</h6>
                    <small class="text-muted">${formatFileSize(file.size)}</small>
                </div>
            `;
            
            fileList.appendChild(fileItem);
            
            // Update submitted file info
            document.getElementById('submittedFileName').textContent = file.name;
            
            // Show preview for images
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('filePreview');
                    preview.innerHTML = `<img src="${e.target.result}" class="file-preview" alt="File preview">`;
                    preview.querySelector('.file-preview').style.display = 'inline-block';
                };
                reader.readAsDataURL(file);
            }
        }
        
        function getFileIcon(fileType) {
            if (fileType.startsWith('image/')) return 'fas fa-file-image';
            if (fileType.includes('pdf')) return 'fas fa-file-pdf';
            if (fileType.includes('word') || fileType.includes('document')) return 'fas fa-file-word';
            if (fileType.includes('zip') || fileType.includes('compressed')) return 'fas fa-file-archive';
            return 'fas fa-file';
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        function createConfetti() {
            const colors = ['#6a11cb', '#2575fc', '#00b09b', '#ff6b6b', '#ffd93d'];
            
            for (let i = 0; i < 100; i++) {
                const confetti = document.createElement('div');
                confetti.classList.add('confetti');
                
                const color = colors[Math.floor(Math.random() * colors.length)];
                const left = Math.random() * 100;
                const size = Math.random() * 10 + 5;
                const animationDuration = Math.random() * 3 + 2;
                
                confetti.style.background = color;
                confetti.style.left = `${left}%`;
                confetti.style.width = `${size}px`;
                confetti.style.height = `${size}px`;
                confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                
                document.body.appendChild(confetti);
                
                // Animate confetti
                const animation = confetti.animate([
                    { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
                    { transform: `translateY(${window.innerHeight}px) rotate(${Math.random() * 720}deg)`, opacity: 0 }
                ], {
                    duration: animationDuration * 1000,
                    easing: 'cubic-bezier(0.215, 0.61, 0.355, 1)'
                });
                
                animation.onfinish = () => {
                    confetti.remove();
                };
            }
        }
    </script>
</body>
</html>