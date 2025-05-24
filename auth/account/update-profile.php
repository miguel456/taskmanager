<?php

require '../common.php';
require '../../bd.php';

$pdo = Database::getConnection();
$errors = [];

if (!is_logged_in()) {
    $errors[] = 'Sem sessão iniciada.';
    response('/error/access-denied.html', 'Unauthorized', $errors);
}

$username = $_POST['username'];
$email = $_POST['email'];



if (empty($username)) {
    $errors[] = 'Username can\'t be empty.';
}

if (empty($email)) {
    $errors[] = 'Email can\'t be empty';
}
else
{
    if (is_null(filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE))) {
        $errors[] = 'Email must be valid.';
    }
}

if (empty($errors)) {
    if ($username !== $_SESSION['username'] || $email !== $_SESSION['email']) {
        update_user($_SESSION['email'],
            [
                'username' => $username,
                'email' => $email
            ],
        );
        $_SESSION['email'] = $email;
        $_SESSION['username'] = $username;
    }

    flash_message("Alterações guardadas", "Os dados do perfil foram atualizados.");
    response('/auth/account/profile.php', 'OK');

}