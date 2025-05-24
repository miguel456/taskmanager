<?php
require '../bd.php';
require 'common.php';

$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

        $stmt = $pdo->prepare('INSERT INTO user (iduser, nome, password, estado, email) VALUES (null, ?, ?, ?, ?)');
        $stmt->execute([
            $username,
            password_hash($password, PASSWORD_BCRYPT),
            0,
            $email
        ]);

        flash_message('Sucesso!', 'Processo de registo terminado com sucesso. Verifique o seu email para ativar a sua nova conta.');
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
}
