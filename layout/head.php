<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= config('AppName') . ' | ' . resolvePageTitle($_SERVER['REQUEST_URI']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/footer-custom.css">
    <?php if(str_contains($_SERVER['REQUEST_URI'], 'dashboard')): ?>
        <link rel="stylesheet" href="/assets/css/dashboard.css">
    <?php endif; ?>

    <?php if(str_contains($_SERVER['REQUEST_URI'], '/tasks/statuses')): ?>
        <link rel="stylesheet" href="/assets/css/task-status.css">
    <?php endif; ?>

    <?php if(str_contains($_SERVER['REQUEST_URI'], '/projects/statuses')): ?>
        <link rel="stylesheet" href="/assets/css/project-status.css">
    <?php endif; ?>

    <?php if(str_contains($_SERVER['REQUEST_URI'], 'profile.php')): ?>
        <link rel="stylesheet" href="/assets/css/profile.css">
    <?php endif; ?>
    <style>
        body { background-color: #f8f9fa; }
        .navbar { margin-bottom: 20px; }
        .card { border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .table th, .table td { vertical-align: middle; }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .main-content {
            flex: 1 0 auto;
        }
    </style>
</head>
