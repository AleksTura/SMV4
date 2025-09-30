<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="izgled.css">
    <title>Naloga</title>
</head>
<body>
    <div class="nalogadiv">
        <h1>Ime predmeta iz PB</h1>
    </div>
    <div class="nalogadiv">
        <h3>IME NALOGE</h3>
        <p>Opis naloge </p><br>
        <div class="DropFile" id="DropFile">
            plac za oddajo naloge- drag and drop za datoteke
        </div>
        <div class ="DroppedFile" style="display: none" id="DroppedFile">ZE ODDANA NALOGA</div>
        <button id="OddajButton" onclick="hide()">ODDAJ</button>
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