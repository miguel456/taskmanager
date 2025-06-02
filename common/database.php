<?php

use App\Core\Database\DataLayer;

require_once realpath(__DIR__ . '/../app/bootstrap.php');

// identifiers = ['iduser', 0] (ultimo arg protegido)
/**
 * Constrói e executa dinâmicamente uma query UPDATE para alterar a dada tabela $table
 * @param string $table Tabela a modificar
 * @param array $identifiers Array de identificadores; coluna da chave primária + valor do WHERE
 * @param array $fillable Array de colunas permitidas
 * @param array $fields Array associativo com colunas e dados a modificar
 * @return bool Sucesso da operação
 * @deprecated Utilize DataLayer::updateTableData() alternativamente
 * @throws Exception
 */
function update_table_data(string $table, array $identifiers, array $fillable, array $fields): bool
{
    return DataLayer::updateTableData($table, $identifiers, $fillable, $fields);
}