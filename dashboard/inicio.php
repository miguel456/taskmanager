<?php
require_once realpath(__DIR__ . '/../app/bootstrap.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .dashboard-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 56px);
        }
        .card {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .badge-success {
            background-color: #28a745;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><i class="fas fa-home"></i> Dashboard</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link nav-active" href="/dashboard/inicio.php"><i class="fa fa-home"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/auth/account/profile.php"><i class="fas fa-user"></i> Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-cog"></i> Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-info-circle"></i> Help</a>
                </li>
            </ul>
            <form class="d-flex">
                <a href="/logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </form>
        </div>
    </div>
</nav>
<div class="dashboard-container">
    <div class="card">
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success w-100 text-center mb-4">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>
        <h5 class="card-title text-center"><i class="fas fa-user-circle"></i> User Information</h5>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email'] ?? 'N/A'); ?></p>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username'] ?? 'N/A'); ?></p>
        <p><strong>Status:</strong> <span class="badge badge-success">Active</span></p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
include '../error/flash-messages.php';

if (!is_logged_in()) {
    response('/error/access-denied.html', 'Unauthorized', [], 401);
    die;
}

?>
</body>
</html>