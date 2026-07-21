<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$reader = IOFactory::createReaderForFile('C:/Users/Ariel/Downloads/cepreuna_2026_II/examen_sabado_cepreuna_2026_II/listado_excel.xls');
$spreadsheet = $reader->load('C:/Users/Ariel/Downloads/cepreuna_2026_II/examen_sabado_cepreuna_2026_II/listado_excel.xls');
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray(null, true, true, true);

$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=admisionv1', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Mapear nombres de programa del Excel a IDs
$progStmt = $pdo->query("SELECT id, UPPER(nombre) as nombre FROM programa");
$programaMap = [];
foreach ($progStmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
    $programaMap[$p['nombre']] = $p['id'];
}

// Filtrar solo ingresantes (apto = SI)
$ingresantes = [];
$first = true;
foreach ($rows as $row) {
    if ($first) { $first = false; continue; }
    $apto = trim($row['G']);
    if ($apto !== 'SI') continue;
    
    $nombrePrograma = mb_strtoupper(trim($row['I']), 'UTF-8');
    $idPrograma = $programaMap[$nombrePrograma] ?? null;
    
    if ($idPrograma === null) {
        echo "ADVERTENCIA: Programa no encontrado: '$nombrePrograma' - DNI: {$row['A']}" . PHP_EOL;
    }
    
    $ingresantes[] = [
        'dni'         => $row['A'],
        'paterno'     => $row['B'],
        'materno'     => $row['C'],
        'nombres'     => $row['D'],
        'puntaje'     => floatval($row['E']),
        'apto'        => 'SI',
        'observacion' => trim($row['H']),
        'id_programa' => $idPrograma,
        'programa'    => $nombrePrograma,
        'id_examen'   => $row['J'],
        'litho'       => $row['K'],
        'numlectura'  => $row['L'],
        'tipo'        => $row['M'],
        'calificado'  => trim($row['N']),
        'aula'        => $row['O'],
        'respuestas'  => $row['P'],
    ];
}

echo "Total ingresantes: " . count($ingresantes) . PHP_EOL;

// Calcular puesto_general (orden de merito global por puntaje descendente)
usort($ingresantes, fn($a, $b) => $b['puntaje'] <=> $a['puntaje']);
foreach ($ingresantes as $i => &$ing) {
    $ing['puesto_general'] = $i + 1;
}
unset($ing);

// Calcular puesto por programa
$porPrograma = [];
foreach ($ingresantes as &$ing) {
    $pid = $ing['id_programa'];
    if (!isset($porPrograma[$pid])) $porPrograma[$pid] = [];
    $porPrograma[$pid][] = &$ing;
}
unset($ing);

foreach ($porPrograma as $pid => &$lista) {
    usort($lista, fn($a, $b) => $b['puntaje'] <=> $a['puntaje']);
    foreach ($lista as $i => &$ing) {
        $ing['puesto'] = $i + 1;
    }
}
unset($ing);

// Insertar
$pdo->exec("DELETE FROM resultados WHERE id_proceso = 34 AND modalidad = 9");

$sql = "INSERT INTO resultados 
    (dni_postulante, id_proceso, paterno, materno, nombres, modalidad, puntaje, apto, observacion, programa, id_examen, litho, numlectura, tipo, calificado, aula, respuestas, fecha, puesto, puesto_general) 
    VALUES 
    (:dni, 34, :paterno, :materno, :nombres, 9, :puntaje, :apto, :observacion, :programa, :id_examen, :litho, :numlectura, :tipo, :calificado, :aula, :respuestas, '2026-07-18', :puesto, :puesto_general)";

$stmt = $pdo->prepare($sql);

$count = 0;
foreach ($ingresantes as $ing) {
    $stmt->execute([
        ':dni'          => str_pad($ing['dni'], 12, ' ', STR_PAD_LEFT),
        ':paterno'      => $ing['paterno'],
        ':materno'      => $ing['materno'],
        ':nombres'      => $ing['nombres'],
        ':puntaje'      => $ing['puntaje'],
        ':apto'         => $ing['apto'],
        ':observacion'  => $ing['observacion'] ?: null,
        ':programa'     => $ing['id_programa'],
        ':id_examen'     => $ing['id_examen'],
        ':litho'        => $ing['litho'],
        ':numlectura'   => $ing['numlectura'],
        ':tipo'         => $ing['tipo'],
        ':calificado'   => $ing['calificado'],
        ':aula'         => $ing['aula'],
        ':respuestas'   => $ing['respuestas'],
        ':puesto'        => $ing['puesto'],
        ':puesto_general'=> $ing['puesto_general'],
    ]);
    $count++;
}

echo "Insertados: $count registros" . PHP_EOL;
$r = $pdo->query("SELECT COUNT(*) FROM resultados WHERE id_proceso = 34 AND modalidad = 9")->fetchColumn();
echo "Total en BD: $r" . PHP_EOL;

// Mostrar algunos resultados de muestra
echo PHP_EOL . "Muestra (top 5 por puntaje):" . PHP_EOL;
$sample = $pdo->query("SELECT dni_postulante, nombres, paterno, programa, puntaje, puesto, puesto_general FROM resultados WHERE id_proceso = 34 AND modalidad = 9 ORDER BY puesto_general LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach ($sample as $s) {
    echo "{$s['puesto_general']}. [P{$s['puesto']}] {$s['dni_postulante']} {$s['paterno']} {$s['nombres']} - Puntaje: {$s['puntaje']} - Programa: {$s['programa']}" . PHP_EOL;
}
