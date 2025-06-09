<?php
require_once realpath(__DIR__ . '/../../app/bootstrap.php');

if (!is_logged_in()) {
    header('Location: /error/access-denied.html');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<?php include_once '../../layout/head.php'?>
<body>

<!-- Navbar -->
<?php include_once '../../layout/nav.php' ?>

<!-- Profile Edit Section -->
<div class="main-content">
    <div class="edit-container">
        <div class="card">
            <h5 class="card-title text-center"><i class="fas fa-user-edit"></i> Edit Profile</h5>
            <form action="update-profile.php" method="POST" novalidate>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" required>
                    <div class="invalid-feedback">Please enter a valid username.</div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Save Changes</button>
            </form>
            <button class="btn btn-secondary w-100 mt-3" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</button>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="change-password.php" method="POST" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                        <div class="invalid-feedback">Please enter your current password.</div>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required>
                        <div class="invalid-feedback">Please enter a new password.</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                        <div class="invalid-feedback">Please confirm your new password.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include '../../layout/footer.php';
include '../../error/flash-messages.php';
?>

</body>
</html>