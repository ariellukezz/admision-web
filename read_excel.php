<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$reader = IOFactory::createReaderForFile('C:/Users/Ariel/Downloads/cepreuna_2026_II/examen_sabado_cepreuna_2026_II/listado_excel.xls');
$spreadsheet = $reader->load('C:/Users/Ariel/Downloads/cepreuna_2026_II/examen_sabado_cepreuna_2026_II/listado_excel.xls');
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray(null, true, true, true);
foreach (array_slice($rows, 0, 10) as $row) {
    echo json_encode($row) . PHP_EOL;
}
echo 'Total filas: ' . count($rows) . PHP_EOL;
