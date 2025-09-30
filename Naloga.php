<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="izgled.css">
    <title>Naloga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="nalogadiv text-center">
                    <h1>Ime predmeta iz PB</h1>
                </div>
                
                <div class="nalogadiv">
                    <h3>IME NALOGE</h3>
                    <p>Opis naloge</p>
                    
                    <div class="d-flex justify-content-center">
                        <div class="DropFile" id="DropFile">
                            <div class="text-center">
                                <i class="fas fa-cloud-upload-alt mb-2" style="font-size: 2rem; color: #0d6efd;"></i>
                                <p class="mb-0">Povleci in spusti datoteko sem</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="DroppedFile" id="DroppedFile">
                        <i class="fas fa-check-circle me-2"></i>NALOGA ODDANA
                    </div>
                    
                    <button id="OddajButton" class="btn btn-primary" onclick="hide()">ODDAJ</button>
                </div>
            </div>
        </div>
    </div>

</body>

<script>
    var DroppedFile = document.getElementById('DroppedFile');
    var DropFileBox = document.getElementById('DropFile');
    var OddajButton = document.getElementById('OddajButton');
    function hide() {
    if(DroppedFile.style.display == "none"){
        DropFileBox.style.display = 'none';
        DroppedFile.style.display = 'block';
        OddajButton.textContent = 'SPREMENI';
    }
    else{
        DropFileBox.style.display = 'flex';
        DroppedFile.style.display = 'none';
        OddajButton.textContent = 'ODDAJ';
    }
}
</script>