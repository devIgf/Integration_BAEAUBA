<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Integration</title>
    <link rel="stylesheet" href="../style/wel.css">
    <link rel="stylesheet" href="../style/waiting.css">

</head>
<body>
    <div class="navbar">
        <div class="logo">
            <img src="../images/log.png" alt="Logo">
        </div>
        <h1 style="color: red" >Integration</h1>
    </div>

    <div class="container">
        <h1>INTEGRATION</h1>

        @if (session('success'))
        <div class="alert success">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="alert error">
            {{ session('error') }}
        </div>
        @endif

        <form id="uploadForm" action="{{ route('moi') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div>
                <label for="json_file">Upload JSON File:</label>
                <input type="file" name="json_file" id="json_file" required>
            </div>
            <button type="submit" id="submitButton">Import</button>
        </form>
    </div>

    <!-- Section de l'animation -->
    <section id="loadingAnimation">
        <div class="loader">
            <span style="--i:1;"></span>
            <span style="--i:2;"></span>
            <span style="--i:3;"></span>
            <span style="--i:4;"></span>
            <span style="--i:5;"></span>
            <span style="--i:6;"></span>
            <span style="--i:7;"></span>
            <span style="--i:8;"></span>
            <span style="--i:9;"></span>
            <span style="--i:10;"></span>
            <span style="--i:11;"></span>
            <span style="--i:12;"></span>
            <span style="--i:13;"></span>
            <span style="--i:14;"></span>
            <span style="--i:15;"></span>
            <span style="--i:16;"></span>
            <span style="--i:17;"></span>
            <span style="--i:18;"></span>
            <span style="--i:19;"></span>
            <span style="--i:20;"></span>
        </div>
    </section>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', function() {
            var submitButton = document.getElementById('submitButton');
            var loadingAnimation = document.getElementById('loadingAnimation');

            // Désactiver le bouton et afficher l'animation
            submitButton.classList.add('disabled');
            submitButton.disabled = true;
            loadingAnimation.classList.add('active');
        });

        // Optionnel: arrêter l'animation une fois la requête terminée (côté serveur)
        window.addEventListener('load', function() {
            var loadingAnimation = document.getElementById('loadingAnimation');
            loadingAnimation.classList.remove('active');
        });
    </script>
</body>
</html>
