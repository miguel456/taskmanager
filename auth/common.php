<?php
require_once realpath(__DIR__ . '/../app/bootstrap.php');

/**
 * Verifica se o utilizador existe através do e-mail.
 * @param string $email O e-mail a verificar.
 * @return bool Se o utilizador existe ou não.
 * @throws Exception
 */
function user_exists(string $email): bool
{
    $conn = Database::getConnection();

    $stmt = $conn->prepare('SELECT * FROM user WHERE email = ?');
    $stmt->execute([$email]);

    return !empty($stmt->fetch(PDO::FETCH_ASSOC));
}

/**
 * Obtém o utilizador selecionado.
 * @param string $email
 * @return array
 * @throws Exception
 */
function get_user(string $email): array
{
    $pdo = Database::getConnection();

    if (user_exists($email)) {
        $stmt = $pdo->prepare('SELECT * FROM user WHERE email = ?');
        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    return [];
}

/**
 * Ativa uma conta, definindo o parâmetro estado para 1.
 * @param string $email
 * @param array $errors Lista de erros atual
 * @return bool True se ativado, False se não ativado
 * @throws Exception
 */
function activate_user(string $email, array &$errors): bool
{
    $pdo = Database::getConnection();

    try {
        if(user_exists($email)) {
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
 * @deprecated Será removida numa versão futura; erros são agora processados na sessão.
 * @see flash_message()
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
function response(string $back, string $hmessage = "OK", array $errors = [], int $code = 200): void
{
    if (empty($errors)) {
        header('Location: ' . $back);
        return;
    }

    header("HTTP/1.1 $code $hmessage");
    header("Location: $back");
}

/**
 * Verifica se o utilizador tem sessão iniciada.
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
 * @return bool Sucesso ou não.
 * @throws Exception
 */
function update_user(string $email, array $fields): bool
{
    $db = Database::getConnection();

    if (user_exists($email)) {
        $user = get_user($email);
    } else {
        return false;
    }


    $fillable = [
        'nome',
        'email'
    ];

    $params = [];
    $setClause = [];

    foreach ($fields as $untrustedFieldName => $untrustedField) {

        if (in_array($untrustedFieldName, $fillable)) {

            $setClause[] = $untrustedFieldName . ' = ?';
            $params[] = $untrustedField;

        }
    }

    if (empty($setClause)) {
        return false;
    }

    $sql = 'UPDATE user SET ' . implode(", ", $setClause) . ' WHERE iduser = ?';
    $stmt = $db->prepare($sql);

    $params[] = $user['iduser'];
    return $stmt->execute($params);
}

/**
 * Atualiza a palavra-passe do utilizador.
 * @param string $email O email da conta
 * @param string $new_password A nova password
 * @return bool Se foi bem-sucedido ou bão
 * @throws Exception
 */
function update_user_password(string $email, string $new_password): bool
{
    $db = Database::getConnection();

    if (user_exists($email)) {
        $user = get_user($email);

        $stmt = $db->prepare('UPDATE user SET password = ? WHERE iduser = ?');
        return $stmt->execute([password_hash($new_password, PASSWORD_DEFAULT), $user['iduser']]);
    }

    return false;
}

/**
 * Gera um código de ativação para o utilizador.
 * @param string $email
 * @return bool
 * @throws Exception
 */
function prepare_verification(string $email): bool
{
    $db = Database::getConnection();

    if (user_exists($email)) {
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
 * @return bool|null True/False dependendo do estado do token; null se o utilizador ou o token não existirem.
 * @throws Exception
 */
function verify_token(string $email, string $token, bool $set_used = true): ?bool
{
    // Precisamos de fazer validação extra aqui sobre as datas atuais e as do token.

    $db = Database::getConnection();

    if (!user_exists($email)) {
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

/**
 * Escreve uma mensagem na sessão atual, que pode ser lida por todas as páginas que a suportam.
 * @param string $message_title Título da mensagem. Pode ser ignorado pela página.
 * @param string $message_body Corpo da mensagem.
 * @param string $type Tipo da mensagem. Pode ser "error", "success" ou "info".
 * @return void
 */
function flash_message(string $message_title, string $message_body, string $type = 'success'): void
{
    // Mensagem do tipo flash - expira imediatamente
    $ttl = 0;
    $id = bin2hex(random_bytes(16));

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!bag_has_message()) {
        $_SESSION['message_bag'] = [];
    }

    $debug_backtrace = debug_backtrace();
    $_SESSION['message_bag'][] = [
        'meta' => [
            'id' => $id,
            'ttl' => $ttl,
            'invoker' => array_shift($debug_backtrace)
        ],

        'title' => $message_title,
        'body' => $message_body,
        'type' => $type
    ];
}

/**
 * Devolve uma lista de erros e mensagens de sucesso do saco de mensagens definido anteriormente, limpando-o no final.
 * @return array
 */
function pull_messages(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['message_bag'])) {
        return [];
    }

    $message_bag = $_SESSION['message_bag'];

    unset($_SESSION['message_bag']);
    return $message_bag;

}

function get_messages(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['message_bag'])) {
        return [];
    }

    $message_bag = $_SESSION['message_bag'];

    // Gerir o tempo de vida da mensagem
    foreach ($message_bag as $key => &$message) {

        if ($message['meta']['ttl'] == 0) {
            unset($message_bag[$key]);
        } else {
            $message['meta']['ttl']--;
        }

    }
    unset($message);

    $_SESSION['message_bag'] = $message_bag;
    return $_SESSION['message_bag'];
}

/**
 * Verifica se existem mensagens no saco
 * @return bool
 */
function bag_has_message(): bool
{
    return isset($_SESSION['message_bag']) && count($_SESSION['message_bag']) !== 0;
}