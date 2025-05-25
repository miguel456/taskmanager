<?php

require_once realpath(__DIR__ . '/../../app/bootstrap.php');


$pdo = Database::getConnection();
$errors = [];


if (!is_logged_in()) {
    $errors[] = 'Sem sessão iniciada.';
    response('/error/access-denied.html', 'Unauthorized', $errors);
}

$username = $_POST['username'];
$email = $_POST['email'];



if (empty($username)) {
    $errors[] = 'O nome de utilizador não pode estar vazio.';
}

if (empty($email)) {
    $errors[] = 'O e-mail não pode estar vazio.';
}
else
{
    if (is_null(filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE))) {
        $errors[] = 'O email tem de corresponder ao formato@correto.';
    }
}

if (empty($errors)) {
    if ($username !== $_SESSION['username'] || $email !== $_SESSION['email']) {
        $update_operation = update_user($_SESSION['email'],
            [
                'nome' => $username,
                'email' => $email
            ],
        );

        if ($update_operation) {
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $username;
        }
        else {
            flash_message('Erro Interno', 'Por motivos desconhecidos não foi possível atualizar os seus dados.', 'error');
            response('/auth/account/profile.php', 'Internal Server Error', [], 500);
            die;
        }
    }

    flash_message("Alterações guardadas", "Os dados do perfil foram atualizados.");
    response('/auth/account/profile.php');

}

foreach ($errors as $error) {

    flash_message('Erro de validação!', $error, 'error');
    response('/auth/account/profile.php', 'Bad Request', [], 400);

}