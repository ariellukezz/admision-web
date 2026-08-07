<?php

/**
 * Script: Generar 500 participantes de prueba + sorteo + seleccionados
 * Ejecutar: php test_500_credenciales.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ParticipantePersonal;
use App\Models\Sorteo;
use App\Models\SorteoSeleccionado;
use App\Models\SorteoCargoConfig;
use App\Models\Cargo;
use App\Models\TipoPersonal;

echo "=== Generando 500 participantes de prueba ===\n";

$nombresM = ['Juan','Carlos','José','Luis','Miguel','Pedro','Jorge','Diego','Fernando','Ricardo','Raúl','Héctor','César','Augusto','Edwin','Walter','Ronald','Félix','Oscar','Manuel','Danilo','Boris','Julio','Marco','Andrés'];
$nombresF = ['María','Ana','Rosa','Carmen','Luz','Elena','Patricia','Sonia','Claudia','Mónica','Ruth','Gloria','Nancy','Vilma','Karina','Roxana','Miriam','Julia','Lucía','Sandra','Yolanda','Teresa','Graciela','Maribel','Angélica'];
$apellidos = ['Mamani','Quispe','Flores','Ramos','Gutiérrez','Vargas','Castillo','Aguirre','Salazar','Condori','Huamán','Cáceres','Apaza','Chura','Colque','Mendoza','Choque','Puma','Apaza','Ticona','Mamani','Quispe','Flores','Ramos','Gutiérrez','Vargas','Castillo','Aguirre','Salazar','Condori'];
$dependencias = ['Facultad de Ingeniería','Facultad de Ciencias Sociales','Facultad de Educación','Facultad de Medicina','Facultad de Derecho','Dirección de Admisión','Biblioteca Central','Laboratorios','Administración Central','Decanato'];
$condiciones = ['NOMBRADO','CONTRATADO','DESTACADO','ENCARGATURA'];
$tipoIds = TipoPersonal::pluck('id')->toArray();
$cargoIds = Cargo::pluck('id')->toArray();
$idProceso = 34;
$idUsuario = 1;

// Crear sorteo de prueba
$sorteo = Sorteo::create([
    'nombre' => 'TEST 500 CREDENCIALES',
    'descripcion' => 'Sorteo para pruebas de generación masiva de credenciales',
    'id_proceso' => $idProceso,
    'id_usuario' => $idUsuario,
    'estado' => true,
]);

// Asociar todos los tipos
$sorteo->tiposPersonal()->sync($tipoIds);

echo "Sorteo creado: ID {$sorteo->id}\n";

// Configurar cargos con cupos amplios (60 cada uno = 540 total)
foreach ($cargoIds as $cid) {
    SorteoCargoConfig::create([
        'id_sorteo' => $sorteo->id,
        'id_cargo' => $cid,
        'cantidad' => 60,
        'id_usuario' => $idUsuario,
    ]);
}

echo "Cargos configurados: " . count($cargoIds) . " cargos × 60 cupos\n";

// Generar 500 participantes
$participantesCreados = 0;
$dniBase = 70000000;

for ($i = 0; $i < 500; $i++) {
    $esMasculino = ($i % 2 == 0);
    $nombresList = $esMasculino ? $nombresM : $nombresF;
    $nombre = $nombresList[array_rand($nombresList)];
    $paterno = $apellidos[array_rand($apellidos)];
    $materno = $apellidos[array_rand($apellidos)];
    $tipoId = $tipoIds[array_rand($tipoIds)];

    $p = ParticipantePersonal::create([
        'dni' => (string)($dniBase + $i),
        'nombres' => $nombre,
        'paterno' => $paterno,
        'materno' => $materno,
        'id_tipo_personal' => $tipoId,
        'codigo_trabajador' => 'T' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
        'condicion' => $condiciones[array_rand($condiciones)],
        'dependencia' => $dependencias[array_rand($dependencias)],
        'foto' => null,
        'estado' => true,
        'id_usuario' => $idUsuario,
    ]);

    // Asignar al sorteo con un cargo rotativo
    $cargoId = $cargoIds[$i % count($cargoIds)];
    SorteoSeleccionado::create([
        'id_sorteo' => $sorteo->id,
        'id_participante' => $p->id,
        'id_cargo' => $cargoId,
        'id_usuario' => $idUsuario,
    ]);
    $participantesCreados++;
}

echo "Participantes creados: {$participantesCreados}\n";
echo "Seleccionados creados: {$participantesCreados}\n";
echo "\n=== Resumen ===\n";
echo "Sorteo ID: {$sorteo->id}\n";
echo "URL de prueba PDF: /admin/sorteo/export-credenciales-pdf?id_sorteo={$sorteo->id}\n";
echo "URL de prueba HTML: /admin/sorteo/credenciales?id_sorteo={$sorteo->id}\n";
echo "Total participantes en BD: " . ParticipantePersonal::count() . "\n";
echo "Total seleccionados en sorteo: " . SorteoSeleccionado::where('id_sorteo', $sorteo->id)->count() . "\n";
echo "\n¡Listo!\n";
