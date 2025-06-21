<?php

use App\Core\Database\Database;
use App\Models\Users\User;

require_once realpath(__DIR__ . '/../app/bootstrap.php');


$pdo = Database::getConnection();

if (direct_check()) {
    header('Location: registo.php');
    die('Acesso inválido. Dados do formulário obrigatórios.');
}

$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['confirm_password'];
$password_confirm = $_POST['password'];


$errors = [];

if (empty($username)) {
    $errors[] = 'O campo Utilizador deve ser preenchido.';
}

if (empty($password)) {
    $errors[] = 'O campo Palavra-passe deve ser preenchido.';
}

if (empty($password_confirm)) {
    $errors[] = 'O campo Confirmar Palavra-passe deve ser preenchido.';
}

if ($password_confirm !== $password) {
    $errors[] = 'As palavras-passe introduzidas não coincidem.';
}

if (empty($email)) {
    $errors[] = 'O campo E-mail deve ser preenchido.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'O email introduzido não é válido.';
}

try {
    if (empty($errors)) {

        if (user_exists($email)) {

            flash_message('Erro de validação', 'O utilizador já existe.', 'error');
            response('/registo.php');
            die;
        }

        $user = new User(null, $username, $email, $password)->create();

        if ($user) {
            flash_message('Sucesso!', 'Processo de registo terminado com sucesso. Verifique o seu email para ativar a sua nova conta.');
            response('/login.php');
            die;
        }

    }
    else
    {
        foreach ($errors as $error) {
            flash_message('Erro de validação', $error, 'error');
        }

    }

    response('/registo.php');

} catch (PDOException $exception) {
    $errors[] = $exception->getMessage();
} catch (LogicException $logicException) {
    $errors[] = $logicException->getMessage();
} catch (Exception $e) {
    $errors[] = $e->getMessage();
}