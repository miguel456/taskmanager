<?php
// É boa prática incluir parâmetros PHPDoc em todas as funções adicionais que declaramos, pois assim o IDE pode facilmente
// fornecer documentação da função.
/**
 * Verifica se o utilizador existe através do e-mail.
 * @param string $email O e-mail a verificar.
 * @param PDO $conn A ligação a utilizar.
 * @return bool Se o utilizador existe ou não.
 */
function user_exists(string $email, PDO $conn): bool
{

    $stmt = $conn->prepare('SELECT * FROM user WHERE email = ?');
    $stmt->execute([$email]);

    return !empty($stmt->fetch(PDO::FETCH_ASSOC));
}

function get_user(string $email, PDO $pdo): array
{
    if (user_exists($email, $pdo)) {
        $stmt = $pdo->prepare('SELECT * FROM user WHERE email = ?');
        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    return [];
}

/**
 * Ativa uma conta, definindo o parâmetro estado para 1.
 * @param string $email
 * @param PDO $pdo
 * @param array $errors Lista de erros atual
 * @return bool True se ativado, False se não ativado
 */
function activate_user(string $email, PDO $pdo, array &$errors): bool
{
    try {
        if(user_exists($email, $pdo)) {
            $stmt = $pdo->prepare("UPDATE user SET estado = ? WHERE email = ?");
            $stmt->execute([
                1,
                $email
            ]);

            return true;
        }
    } catch (PDOException $exception) {
        $errors[] = 'Não foi possível ativar a sua conta: Erro ' . $exception->getCode() . '.';
    }

    return false;
}

/**
 * Processar os erros.
 * @param array $errors
 * @return string
 */
function pack_errors(array $errors): string
{
    return base64_encode(json_encode($errors));
}


/**
 * Função rudimentar para enviar respostas.
 * @param string $back O URL para onde enviar o utilizador
 * @param string $hmessage A mensagem HTTP
 * @param array $errors Array de erros
 * @param int $code Código de erro
 * @return void
 */
function response($back, $hmessage, array $errors = [], int $code = 200): void
{
    if (empty($errors)) {
        header('Location: ' . $back);
        return;
    }

    header("HTTP/1.1 $code $hmessage");
    header("Location: $back/?errors=" . pack_errors($errors));
}

/**
 * Verifica se o utilizador tem sessão iniciada.
 * @param string $email O email do utilizador.
 * @return bool Se tem sessão iniciada ou não.
 */
function is_logged_in(): bool
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true;
}

/**
 * Atualiza um ou mais campos do utilizador.
 * @param string $email O email associado à conta.
 * @param array $fields A lista associativa de campos, comparados com uma lista de campos autorizados, a atualizar com os respetivos valores.
 * @param PDO $db A ligação à base de dados.
 * @return bool Sucesso ou não.
 */
function update_user(string $email, array $fields, PDO $db): bool
{
    $fillable = [
        'nome',
        'email'
    ];

    $params = [];
    $setClause = [];

    foreach ($fields as $untrustedFieldName => $untrustedField) {

        foreach ($fillable as $fillableField) {

            if (array_key_exists($untrustedFieldName, $fillable)) {

                $setClause[] = $fillableField . ' = ?';
                $params[] = $untrustedField;

            }
        }
    }

    $sql = 'UPDATE user SET ' . implode(" ", $setClause) . 'WHERE iduser = ?';
    $stmt = $db->prepare($sql);

    $stmt->execute($params);

}

/**
 * Gera um código de ativação para o utilizador.
 * @param string $email
 * @param PDO $db
 * @return bool
 */
function prepare_verification(string $email, PDO $db): bool
{
    if (user_exists($email, $db)) {
        $user = get_user($email, $db);
        $key = bin2hex(openssl_random_pseudo_bytes(64));

        if ($user['estado'] == 0) {
            $stmt = $db->prepare('INSERT INTO user_verification (id, iduser, verification_code, status, ttl, created_at, updated_at, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ');
            $stmt->execute([
               null,
               $user['iduser'],
               $key,
               'unused',
               date('Y-m-d H:i:s', strtotime('+24 hours')),
               date('Y-m-d H:i:s'),
               date('Y-m-d H:i:s'),
                null,
                null
            ]);

            return $key;
        }
    }

    return false;
}

/**
 * Verifica se o código de verificação é válido e, opcionalmente, marca-o como usado.
 * @param string $email O email do utilizador
 * @param string $token O token do utilizador
 * @param bool $set_used Marcar como usado ou não
 * @params PDO $db A ligação à bd
 * @return bool|null True/False dependendo do estado do token; null se o utilizador ou o token não existirem.
 */
function verify_token(string $email, string $token, PDO $db, bool $set_used = true): ?bool
{
    // Precisamos de fazer validação extra aqui sobre as datas atuais e as do token.

    if (!user_exists($email, $db)) {
        return null;
    }

    $user = get_user($email, $db);

    $stmt = $db->prepare('SELECT * FROM user_verification ORDER BY ttl DESC WHERE iduser = ?');
    $stmt->execute([$user['iduser']]);
    $internal_token = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($internal_token && $internal_token['verification_code'] === $token && $internal_token['status'] === 'unused' && strtotime($internal_token['ttl']) > time()) {
        if ($set_used) {
            $stmt = $db->prepare('UPDATE user_verification SET status = "used" WHERE id = ?');
            $stmt->execute([$internal_token['id']]);
        }
        return true;
    }
    return false;
}

