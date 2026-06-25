<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrar registros existentes desde los campos planos de postulante a revision_solicitudes
        $postulantes = DB::table('postulante')
            ->where('revision_solicitada', true)
            ->orWhereNotNull('revision_solicitada_at')
            ->get();

        foreach ($postulantes as $p) {
            // Buscar preinscripción para obtener modalidad, programa y proceso
            $preinscripcion = DB::table('pre_inscripcion')
                ->where('id_postulante', $p->id)
                ->latest('id')
                ->first();

            // Si no hay preinscripción, buscar en inscripciones
            $idModalidad = $preinscripcion?->id_modalidad;
            $idPrograma = $preinscripcion?->id_programa;
            $idProceso = $preinscripcion?->id_proceso;

            if (!$idProceso) {
                $inscripcion = DB::table('inscripciones')
                    ->where('id_postulante', $p->id)
                    ->where('estado', 1)
                    ->latest('id')
                    ->first();
                $idPrograma = $idPrograma ?? $inscripcion?->id_programa;
                $idProceso = $inscripcion?->id_proceso ?? 0;
            }

            // Determinar estado
            $estado = 'solicitada';
            if ($p->revision_finalizada_at) {
                $estado = 'completada';
            } elseif ($p->revision_iniciada_at) {
                $estado = 'en_revision';
            }

            $solicitadaAt = $p->revision_solicitada_at ?: now();

            DB::table('revision_solicitudes')->insert([
                'id_postulante' => $p->id,
                'id_proceso' => $idProceso,
                'id_modalidad' => $idModalidad,
                'id_programa' => $idPrograma,
                'veces' => $p->veces_revision_solicitada ?? 1,
                'solicitada_at' => $solicitadaAt,
                'iniciada_at' => $p->revision_iniciada_at,
                'finalizada_at' => $p->revision_finalizada_at,
                'revisor_id' => $p->revision_revisor_id,
                'apto' => $p->revision_apto ?? false,
                'apto_at' => $p->revision_apto_at,
                'datos_citacion' => $p->datos_citacion,
                'documentos_verificados' => null,
                'documentos_pendientes' => null,
                'estado' => $estado,
                'created_at' => $solicitadaAt,
                'updated_at' => now(),
            ]);

            // Marcar tiene_revision_activa en postulante
            $tieneRevisionActiva = $p->revision_solicitada && !$p->revision_finalizada_at;
            DB::table('postulante')
                ->where('id', $p->id)
                ->update(['tiene_revision_activa' => $tieneRevisionActiva]);
        }
    }

    public function down(): void
    {
        DB::table('revision_solicitudes')->truncate();
        DB::table('postulante')->update(['tiene_revision_activa' => false]);
    }
};
