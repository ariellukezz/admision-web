<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ParticipantePersonal;
use App\Models\SorteoSeleccionado;
use App\Models\Cargo;

$cargoIds = Cargo::pluck('id')->toArray();
$participantes = ParticipantePersonal::all(['id']);
$idUsuario = 1;
$sorteoId = 2;

echo "Asignando {$participantes->count()} participantes al sorteo {$sorteoId}...\n";

$created = 0;
foreach ($participantes as $i => $p) {
    SorteoSeleccionado::create([
        'id_sorteo' => $sorteoId,
        'id_participante' => $p->id,
        'id_cargo' => $cargoIds[$i % count($cargoIds)],
        'id_usuario' => $idUsuario,
    ]);
    $created++;
}

echo "Asignados: {$created}\n";
echo "Con foto: " . ParticipantePersonal::whereIn('id', SorteoSeleccionado::where('id_sorteo', $sorteoId)->pluck('id_participante'))->whereNotNull('foto')->count() . "\n";
echo "Total sorteo: " . SorteoSeleccionado::where('id_sorteo', $sorteoId)->count() . "\n";
echo "¡Listo!\n";
