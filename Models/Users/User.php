<?php

namespace App\Models\Users;

use App\Core\Database\Database;
use App\Core\Database\DataLayer;
use DateTime;
use Exception;
use LogicException;
use PDO;

class User
{
    private string $nome, $email, $estado;

    private string $password {
        get {
            return $this->password;
        }

        set(string $value) {
            $this->password = password_hash($value, PASSWORD_BCRYPT);
        }
    }

    private ?int $iduser;
    private DateTime $data_criacao;

    private PDO $conn;


    /**
     * Atribui os parâmetros necessários à criação de um utilizador mais tarde.
     * ** ATENÇÃO: ** NÃO passar palavras-passe já em hash! O construtor já faz isso.
     * @param int|null $iduser ID do utilizador, se estivermos a usar um user já existente
     * @param string $nome Nome do utilizador
     * @param string $password Palavra-passe a "encriptar"
     * @param int $estado Estado, booleano, controla a capacidade de fazer login
     * @throws LogicException|Exception Caso o ID seja definido e o utilizador associado não exista
     */
    public function __construct(?int $iduser = null, string $nome = '', $email = '', string $password = '', int $estado = 0)
    {
        if (!is_null($iduser) && !empty($this->getUserById($iduser))) {
            throw new LogicException('O utilizador especificado não existe!');
        }

        $this->iduser = $iduser;
        $this->nome = $nome;
        $this->email = $email;
        $this->password = $password;
        $this->estado = $estado;
        $this->data_criacao = new DateTime();

        $this->conn = Database::getConnection();
    }

    /**
     * Ativa uma conta, definindo o parâmetro estado para 1 (código ainda não testado)
     * @param string $email
     * @return bool True se ativado, False se não ativado
     * @throws Exception
     */
    public function activateUser(string $email): bool
    {
        $pdo = $this->conn;

        if($this->userExists($email)) {
            $stmt = $pdo->prepare("UPDATE user SET estado = ? WHERE email = ?");
            $stmt->execute([
                1,
                $email
            ]);

            return true;
        }

        return false;
    }

    /**
     * Obter um utilizador através do ID.
     * @param int $id O id a procurar.
     * @return array Utilizador, ou FALSE se não houver resultados
     */
    public function getUserById(int $id): array
    {
        $pdo = $this->conn;

        $stmt = $pdo->prepare('SELECT * FROM user WHERE iduser = ?');
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtém o utilizador selecionado.
     * @param string $email
     * @return array
     * @throws Exception
     */
    function getUser(string $email): array
    {
        $pdo = $this->conn;

        if ($this->userExists($email)) {
            $stmt = $pdo->prepare('SELECT * FROM user WHERE email = ?');
            $stmt->execute([$email]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * Devolve todos os utilizadores registados.
     * @param bool $activeOnly Só devolver utilizadores válidos?
     * @return array Lista de utilizadores
     */
    function getAllUsers(bool $activeOnly = false): array
    {
        $pdo = $this->conn;
        $query = 'SELECT * FROM user';

        if ($activeOnly) {
            $query .= ' WHERE estado = 1';
        }

        $stmt = $pdo->prepare($query);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se o utilizador existe através do e-mail.
     * @param string $email O e-mail a verificar.
     * @return bool Se o utilizador existe ou não.
     * @throws Exception
     */
    function userExists(string $email): bool
    {
        $conn = $this->conn;

        $stmt = $conn->prepare('SELECT * FROM user WHERE email = ?');
        $stmt->execute([$email]);

        return !empty($stmt->fetch(PDO::FETCH_ASSOC));
    }

    /**
     * Cria um utilizador com base nos parâmetros fornecidos anteriormente
     * @return bool Sucesso da operação
     */
    public function create(): bool
    {
        $stmt = $this->conn->prepare('INSERT INTO user (nome, password, estado, email, data_criacao) VALUES (?, ?, ?, ?, ?)');
        return $stmt->execute([
            $this->nome,
            $this->password,
            $this->estado,
            $this->email,
            $this->data_criacao->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Atualiza um ou mais campos do utilizador.
     * @param string $email O email associado à conta.
     * @param array $fields A lista associativa de campos, comparados com uma lista de campos autorizados, a atualizar com os respetivos valores.
     * @return bool Sucesso ou não.
     * @throws Exception
     */
    function update(string $email, array $fields): bool
    {
        $db = Database::getConnection();

        if ($this->userExists($email)) {
            $user = $this->getUser($email);
        } else {
            return false;
        }


        $fillable = [
            'nome',
            'email'
        ];

        return DataLayer::updateTableData('user', ['iduser', $user['iduser']], $fillable, $fields);
    }

    /**
     * Atualiza a palavra-passe do utilizador.
     * @param string $email O email da conta
     * @param string $new_password A nova password
     * @return bool Se foi bem-sucedido ou bão
     * @throws Exception
     */
    function updatePassword(string $email, string $new_password): bool
    {
        $db = Database::getConnection();

        if ($this->userExists($email)) {
            $user = $this->getUser($email);

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
    function prepareVerification(string $email): bool
    {
        $db = Database::getConnection();

        if ($this->userExists($email)) {
            $user = $this->getUser($email);
            $key = bin2hex(random_bytes(64));

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
    function verifyToken(string $email, string $token, bool $set_used = true): ?bool
    {
        // Precisamos de fazer validação extra aqui sobre as datas atuais e as do token.

        $db = Database::getConnection();

        if (!$this->userExists($email)) {
            return null;
        }

        $user = $this->getUser($email);

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
     * Verifica se o utilizador tem sessão iniciada
     * @return bool Estado da sessão
     */
    public function loggedIn(): bool
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true;
    }
}

