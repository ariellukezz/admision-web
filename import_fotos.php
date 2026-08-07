<?php

/**
 * Script: Copiar fotos de rrhh2018 a storage usando cod como nombre
 * Las fotos están nombradas por cod de trabajador: 980509.jpg
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ParticipantePersonal;

$sourceDir = 'C:/Users/Ariel/Documents/Ariel/Admisión 2024/DAD 10/rrhh2018';
$destDir   = storage_path('app/public/participantes/fotos');

// Leer relación cod -> dni desde SQLite
$pdo = new PDO('sqlite:C:/Users/Ariel/Documents/Ariel/Admisión 2024/DAD 10/Personal/trabajador.sqlite');
$rows = $pdo->query("SELECT cod, dni FROM trabajador WHERE cod IS NOT NULL AND cod != ''")->fetchAll(PDO::FETCH_ASSOC);

$codToDni = [];
foreach ($rows as $row) {
    $codToDni[trim($row['cod'])] = trim($row['dni']);
}

echo "=== Copiando fotos (por cod) ===\n";
echo "Registros en SQLite: " . count($codToDni) . "\n";

$copiadas = 0;
$noEncontradas = 0;
$actualizadas = 0;

foreach ($codToDni as $cod => $dni) {
    if (empty($dni)) continue;

    $sourceFile = $sourceDir . '/' . $cod . '.jpg';

    if (!file_exists($sourceFile)) {
        $noEncontradas++;
        continue;
    }

    // Nombre destino = DNI.jpg
    $destFile = $destDir . '/' . $dni . '.jpg';

    if (!file_exists($destFile)) {
        copy($sourceFile, $destFile);
        $copiadas++;
    }

    // Actualizar BD
    $relativePath = '/storage/participantes/fotos/' . $dni . '.jpg';
    $p = ParticipantePersonal::where('dni', $dni)->first();
    if ($p && $p->foto !== $relativePath) {
        $p->foto = $relativePath;
        $p->save();
        $actualizadas++;
    }
}

echo "\nResultados:\n";
echo "  Fotos copiadas: {$copiadas}\n";
echo "  BD actualizada: {$actualizadas}\n";
echo "  Sin foto encontrada: {$noEncontradas}\n";
echo "  Total con foto en BD: " . ParticipantePersonal::whereNotNull('foto')->count() . "\n";
echo "\n¡Listo!\n";
