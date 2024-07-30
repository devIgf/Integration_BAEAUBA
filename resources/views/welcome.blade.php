<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Integration</title>
    <link rel="stylesheet" href="../style/wel.css">
    <style>

    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo">
            <img src="../images/log.png" alt="Logo">
        </div>
        <h1>Integration</h1>
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

        <form action="{{ route('moi') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div>
                <label for="json_file">Upload JSON File:</label>
                <input type="file" name="json_file" id="json_file" required>
            </div>
            <button type="submit">Import</button>
        </form>
    </div>
</body>
</html>
