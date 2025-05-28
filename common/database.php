<?php

use App\Core\Database\Database;

require_once realpath(__DIR__ . '/../app/bootstrap.php');

// identifiers = ['iduser', 0] (ultimo arg protegido)
/**
 * Constrói e executa dinâmicamente uma query UPDATE para alterar a dada tabela $table
 * @param string $table Tabela a modificar
 * @param array $identifiers Array de identificadores; coluna da chave primária + valor do WHERE
 * @param array $fillable Array de colunas permitidas
 * @param array $fields Array associativo com colunas e dados a modificar
 * @return bool Sucesso da operação
 * @throws Exception
 */
function update_table_data(string $table, array $identifiers, array $fillable, array $fields): bool
{
    $db = Database::getConnection();

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

    // $table e identifiers virão sempre do nosso código, de momento não é preciso preocupar-nos com estas variáveis
    $sql = 'UPDATE ' . $table . ' SET ' . implode(", ", $setClause) . ' WHERE ' . $identifiers[0] . ' = ?';
    $stmt = $db->prepare($sql);

    $params[] = $identifiers[1];
    return $stmt->execute($params);
}