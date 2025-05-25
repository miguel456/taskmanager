<?php
require_once realpath(__DIR__ . '/../../app/bootstrap.php');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response('/auth/account/profile.php', 'Temporary Redirect', [], 307);
}

if (!is_logged_in()) {
    response('/login.php', 'Unauthorized', [], 401);
    die;
}

// TODO: demasiada validação igual em todos os ficheiros, mover para uma função/ficheiro comum

$errors = [];

$current_password = $_POST['current_password'];

$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];


if (empty($current_password)) {
    $errors[] = 'A sua palavra-passe atual é obrigatória para operações sensíveis.';
}

if (empty($new_password)) {
    $errors[] = 'Forneça uma palavra-passe nova!';
}

if (empty($confirm_password) || $confirm_password !== $new_password) {
    $errors[] = 'As palavras-passe devem coincidir!';
}

if (empty($errors)) {

    $user = get_user($_SESSION['email']);

    if (!password_verify($current_password, $user['password'])) {
        flash_message('Palavra-passe incorreta', 'A palavra-passe atual introduzida não corresponde aos nossos registos. Tente novamente.', 'error');

        response('/auth/account/profile.php', 'Forbidden', [], 403);
        die;
    }

    if (update_user_password($user['email'], $confirm_password)) {
        flash_message('Palavra-passe atualizada', 'A sua palavra-passe foi atualizada. Tenha em mente que outras sessões continuarão ativas.');

        response('/auth/account/profile.php');
        die;
    }

    flash_message('Palavra-passe inalterada!', 'Por motivos desconhecidos, não foi possível alterar a sua palavra-passe', 'error');
    response('/auth/account/profile.php');
    die;

}

foreach ($errors as $error) {
    flash_message('Erro de validação', $error, 'error');
}
response('/auth/account/profile.php');




