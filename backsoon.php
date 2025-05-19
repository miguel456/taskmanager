<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 Service Unavailable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
            margin: 0;
        }
        .error-container {
            text-align: center;
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #dc3545;
        }
        .error-message {
            font-size: 1.5rem;
            color: #6c757d;
        }
        .btn-retry {
            margin-top: 20px;
        }
        .error-image {
            max-width: 200px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="error-container">
    <img src="img/error.svg" width="400px" height="auto" alt="Error Image" class="error-image">
    <div class="error-code">503</div>
    <div class="error-message">
        <?php
        echo isset($_GET['message']) ? htmlspecialchars(base64_decode($_GET['message'])) : 'Serviço Indisponível';
        ?>
    </div>
    <p class="text-muted">Estamos a realizar uma manutenção técnica de momento. Por-favor tente novamente mais tarde.</p>
    <button onclick="window.location.href='registo.php'" class="btn btn-primary btn-retry">Tentar novamente</button>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>