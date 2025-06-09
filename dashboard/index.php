<?php
require_once realpath(__DIR__ . '/../app/bootstrap.php');
?>
<!DOCTYPE html>
<html lang="en">
<?php include_once '../layout/head.php' ?>
<body>
<?php include_once '../layout/nav.php' ?>

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

<?php
include '../layout/footer.php';
include '../error/flash-messages.php';
?>
</body>
</html>