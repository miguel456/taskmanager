<?php global $pdo;

require 'bd.php';
require 'auth/common.php';

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
    $errors[] = 'Utilizador obrigatório';
}

if (empty($password)) {
    $errors[] = 'Password obrigatório';
}

if (empty($password_confirm)) {
    $errors[] = 'Confirme a password!';
}

if ($password_confirm !== $password) {
    $errors[] = 'As passwords não coincidem.';
}

if (empty($email)) {
    $errors[] = 'Email obrigatório';
}


try {

    // Verifica se realmente o utilizador existe utilizando a função que declaramos acima. Passamos o email e a ligação PDO.
    // Caso se verifique simplesmente inserimos um erro no array de erros e fazemos o redirecionamento. Paramos a execução
    // do script com "die" caso o browser do utilizador ignore o header que vamos enviar.
    if (user_exists($email, $pdo)) {
        $errors[] = 'O utilizador já existe.';
        header('Location: registo.php?errors=' . pack_errors($errors));
        die;
    }

    if(empty($errors)) {

        $stmt = $pdo->prepare('INSERT INTO user (iduser, nome, password, estado, email) VALUES (null, ?, ?, ?, ?)');
        $stmt->execute([
            $username,
            password_hash($password, PASSWORD_BCRYPT),
            0,
            $email
        ]);

        header('Location: registo.php?mensagem=Sucesso');
    }
    else
    {
        $string_errors = base64_encode(json_encode($errors));
        header('Location: registo.php?errors=' . $string_errors);
    }

} catch (PDOException $exception) {
    $errors[] = $exception->getMessage();
}
