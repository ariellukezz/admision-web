<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$reader = IOFactory::createReaderForFile('C:/Users/Ariel/Downloads/cepreuna_2026_II/examen_sabado_cepreuna_2026_II/listado_excel.xls');
$spreadsheet = $reader->load('C:/Users/Ariel/Downloads/cepreuna_2026_II/examen_sabado_cepreuna_2026_II/listado_excel.xls');
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray(null, true, true, true);

$first = true;
$counts = [];
foreach ($rows as $row) {
    if ($first) { $first = false; continue; }
    $val = trim($row['G']);
    if (!isset($counts[$val])) $counts[$val] = 0;
    $counts[$val]++;
}
echo "Valores columna G (apto):" . PHP_EOL;
foreach ($counts as $k => $v) {
    echo "'$k' => $v" . PHP_EOL;
}
echo "Total: " . array_sum($counts) . PHP_EOL;
