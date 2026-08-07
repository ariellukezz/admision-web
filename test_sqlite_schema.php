<?php

$pdo = new PDO('sqlite:C:/Users/Ariel/Documents/Ariel/Admisión 2024/DAD 10/Personal/trabajador.sqlite');

// Show all tables
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables: " . implode(', ', $tables) . "\n\n";

// Get schema
foreach ($tables as $table) {
    $cols = $pdo->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
    echo "Table: $table\n";
    foreach ($cols as $col) {
        echo "  - {$col['name']} ({$col['type']})" . ($col['pk'] ? ' PK' : '') . "\n";
    }
    echo "\n";
}

// Show 3 rows
foreach ($tables as $table) {
    $rows = $pdo->query("SELECT * FROM $table LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    echo "Sample rows from $table:\n";
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
}
