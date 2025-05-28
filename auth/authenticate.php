<?php

require_once realpath(__DIR__ . '/../app/bootstrap.php');

$pdo = Database::getConnection();

$email = $_POST['email'];
$password = $_POST['password'];

$errors = [];
if(empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'O e-mail é obrigatório e tem de corresponder ao formato@correto.';
}

if(empty($password)) {
    $errors[] = 'A palavra-passe é obrigatória.';
}

if (!empty($errors)) {
    foreach ($errors as $error) {
        flash_message('Erro de autenticação', $error, 'error');
    }

    response('/login.php', 'Unauthorized', [], 401);
    die;
}

if (!user_exists($email)) {
    flash_message('Erro de autenticação', 'Credenciais inválidas ou conta inativa.');
    response('/login.php', 'Unauthorized', [], 401);
}

$user = get_user($email);

if(password_verify($password, $user['password']) && $user['estado'] == 1) {
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $user['nome'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['id'] = $user['iduser'];

    flash_message('Bem-vindo ' . $_SESSION['username'] . '!', 'Tem agora a sessão iniciada.');
    response('/dashboard/index.php');

}
else
{
    flash_message('Erro de autenticação', 'Credenciais inválidas ou conta inativa.', 'error');
    response('/login.php', 'Unauthorized', [], 401);
}

die('Falha no redirecionamento!');