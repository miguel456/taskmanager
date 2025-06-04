<?php

namespace App\Core\Database;

class DataLayer extends Database
{
    public static function updateTableData(string $table, array $identifiers, array $fillable, array $fields): bool
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

}