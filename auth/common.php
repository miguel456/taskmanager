<?php

use App\Models\Users\User;

require_once realpath(__DIR__ . '/../app/bootstrap.php');

/**
 * Verifica se o utilizador existe através do e-mail.
 * @param string $email O e-mail a verificar.
 * @return bool Se o utilizador existe ou não.
 * @throws Exception
 * @deprecated Utilizar o pseudo-modelo
 * @see User::userExists()
 */
function user_exists(string $email): bool
{
    return (new User()->userExists($email));
}

/**
 * Obtém o utilizador selecionado.
 * @param string $email
 * @return array
 * @throws Exception
 * @deprecated Utilizar o pseudo-modelo
 * @see User::getUser()
 */
function get_user(string $email): array
{
    return (new User()->getUser($email));
}

/**
 * Obter um utilizador através do ID.
 * @param int $id O id a procurar.
 * @return array Utilizador, ou FALSE se não houver resultados
 * @deprecated Utilizar o pseudo-modelo
 * @see \App\Models\Users\User::getUserById()
 */
function get_user_id(int $id): array
{
    return (new User()->getUserById($id));
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
 * @deprecated Usar o pseudo modelo
 * @see User::loggedIn()
 */
function is_logged_in(): bool
{
    return (new User()->loggedIn());
}

/**
 * Atualiza um ou mais campos do utilizador.
 * @param string $email O email associado à conta.
 * @param array $fields A lista associativa de campos, comparados com uma lista de campos autorizados, a atualizar com os respetivos valores.
 * @return bool Sucesso ou não.
 * @deprecated Usar pseudomodelo
 * @see User::update()
 * @throws Exception
 */
function update_user(string $email, array $fields): bool
{
    return (new User()->update($email, $fields));
}

/**
 * Atualiza a palavra-passe do utilizador.
 * @param string $email O email da conta
 * @param string $new_password A nova password
 * @return bool Se foi bem-sucedido ou bão
 * @deprecated Usar pseudomodelo
 * @see User::updatePassword()
 * @throws Exception
 */
function update_user_password(string $email, string $new_password): bool
{
    return (new User()->updatePassword($email, $new_password));
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

function direct_check(): bool
{
    return $_SERVER['REQUEST_METHOD'] !== 'POST';
}