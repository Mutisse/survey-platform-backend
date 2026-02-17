<?php
// Nome do arquivo que baixou do phpMyAdmin
$mysqlFile = __DIR__ . '/survey_platform.sql';  // Usa o caminho absoluto
$pgsqlFile = __DIR__ . '/survey_platform_pgsql.sql';

echo "Convertendo $mysqlFile para PostgreSQL...\n";

if (!file_exists($mysqlFile)) {
    die("ERRO: Arquivo $mysqlFile não encontrado!\n");
}

$mysql = file_get_contents($mysqlFile);
$pgsql = $mysql;

// 1. Remover backticks
$pgsql = str_replace('`', '"', $pgsql);

// 2. Converter AUTO_INCREMENT para SERIAL
$pgsql = preg_replace('/AUTO_INCREMENT=\d+/', '', $pgsql);

// 3. Converter tipos de dados
$conversoes = [
    'int(11)' => 'INTEGER',
    'int(10)' => 'INTEGER',
    'tinyint(1)' => 'BOOLEAN',
    'tinyint(4)' => 'SMALLINT',
    'bigint(20)' => 'BIGINT',
    'varchar(191)' => 'VARCHAR(191)',
    'longtext' => 'TEXT',
    'mediumtext' => 'TEXT',
    'text' => 'TEXT',
    'datetime' => 'TIMESTAMP',
    'timestamp' => 'TIMESTAMP',
    'double' => 'DOUBLE PRECISION',
    'decimal' => 'DECIMAL',
    'float' => 'REAL',
    'enum(' => 'VARCHAR(50) CHECK (',
];

foreach ($conversoes as $mysqlType => $pgsqlType) {
    $pgsql = str_replace($mysqlType, $pgsqlType, $pgsql);
}

// 4. Converter ENGINE e CHARSET
$pgsql = preg_replace('/ENGINE=InnoDB DEFAULT CHARSET=[a-z0-9]+/', '', $pgsql);
$pgsql = preg_replace('/COLLATE=[a-z0-9_]+/', '', $pgsql);

// 5. Ajustar INSERTs para PostgreSQL
$pgsql = str_replace("'0000-00-00 00:00:00'", 'NULL', $pgsql);
$pgsql = str_replace("'0000-00-00'", 'NULL', $pgsql);

// Salvar
file_put_contents($pgsqlFile, $pgsql);

echo "Conversão concluída! Arquivo salvo como: $pgsqlFile\n";
echo "Tamanho original: " . round(filesize($mysqlFile)/1024/1024, 2) . " MB\n";
echo "Tamanho convertido: " . round(filesize($pgsqlFile)/1024/1024, 2) . " MB\n";
?>
