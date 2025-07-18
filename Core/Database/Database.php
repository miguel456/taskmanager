<?php

namespace App\Core\Database;
use Exception;
use PDOException;
use PDO;

require_once realpath(__DIR__ . '/../../app/bootstrap.php');


class Database // credenciais NUNCA devem estar no código
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    /**
     * @throws Exception
     */
    public static function getConnection(): PDO
    {

        $config = parse_ini_file(realpath(__DIR__ . '/../../config/database.ini'), true);

        if ($config) {
            $host = $config['Connection']['Host'] ?? 'localhost';
            $dbname = $config['Connection']['Database'];
            $port = $config['Connection']['Port'] ?? 3306;

            $username = $config['Connection']['Username'] ?? 'root';
            $password = $config['Connection']['Password'];

            $debug = $config['General']['Debug'] ?? false;
        } else {
            throw new Exception('Configuração inválida!');
        }

        if (self::$connection === null) {
            try {
                self::$connection = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);

                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            } catch (PDOException $e) {
                $message = ($debug) ? base64_encode($e->getMessage()) : base64_encode('Base de dados temporáriamente indisponível.');
                header('Location: backsoon.php?message=' . $message);
                die('Base de dados temporariamente indisponível.');
            }
        }
        return self::$connection;
    }
}
