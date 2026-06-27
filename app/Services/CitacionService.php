<?php

namespace App\Services;

use App\Models\ConfiguracionCitacion;
use App\Models\Preinscripcion;
use App\Models\Programa;
use App\Models\Modalidad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CitacionService
{
    /**
     * Calcula la citación automática para un postulante basándose en las
     * configuraciones activas del proceso, con prioridad:
     * dni_digito > programa > modalidad > area > mismo_dia
     */
    public function calcularCitacion(int $idProceso, string $dni, ?int $idPrograma = null, ?int $idModalidad = null): ?ConfiguracionCitacion
    {
        $configuraciones = ConfiguracionCitacion::where('id_proceso', $idProceso)
            ->where('estado', true)
            ->orderByRaw("FIELD(tipo_criterio, 'dni_digito', 'programa', 'modalidad', 'area', 'mismo_dia')")
            ->get();

        if ($configuraciones->isEmpty()) {
            return null;
        }

        // Resolver datos del postulante si no se pasaron
        if (!$idPrograma || !$idModalidad) {
            $preinscripcion = Preinscripcion::where('id_postulante', function ($query) use ($dni) {
                $query->select('id')
                    ->from('postulante')
                    ->where('nro_doc', $dni)
                    ->limit(1);
            })
            ->where('id_proceso', $idProceso)
            ->latest()
            ->first();

            if ($preinscripcion) {
                $idPrograma = $idPrograma ?? $preinscripcion->id_programa;
                $idModalidad = $idModalidad ?? $preinscripcion->id_modalidad;
            }
        }

        // Obtener área y nombres si hay programa
        $areaPostulante = null;
        $nombrePrograma = null;
        $nombreModalidad = null;

        if ($idPrograma) {
            $programa = Programa::find($idPrograma);
            if ($programa) {
                $areaPostulante = $programa->area;
                $nombrePrograma = $programa->nombre;
            }
        }

        if ($idModalidad) {
            $modalidad = Modalidad::find($idModalidad);
            if ($modalidad) {
                $nombreModalidad = $modalidad->nombre;
            }
        }

        $ultimoDigito = substr($dni, -1);
        $ultimosDosDigitos = substr($dni, -2);

        foreach ($configuraciones as $config) {
            switch ($config->tipo_criterio) {
                case 'dni_digito':
                    if ($this->matchDniDigito($config->valor, $ultimoDigito, $ultimosDosDigitos)) {
                        return $config;
                    }
                    break;

                case 'programa':
                    if ($idPrograma && (string) $config->valor === (string) $idPrograma) {
                        return $config;
                    }
                    if ($nombrePrograma && strcasecmp(trim($config->valor), trim($nombrePrograma)) === 0) {
                        return $config;
                    }
                    break;

                case 'modalidad':
                    if ($idModalidad && (string) $config->valor === (string) $idModalidad) {
                        return $config;
                    }
                    if ($nombreModalidad && strcasecmp(trim($config->valor), trim($nombreModalidad)) === 0) {
                        return $config;
                    }
                    break;

                case 'area':
                    if ($areaPostulante && strcasecmp(trim($config->valor), trim($areaPostulante)) === 0) {
                        return $config;
                    }
                    break;

                case 'mismo_dia':
                    // Catch-all: aplica a todos
                    return $config;
            }
        }

        return null;
    }

    /**
     * Verifica si el dígito del DNI coincide con el valor configurado.
     * Soporta formatos: "3", "0-1", "8-9", "00-01" (2 dígitos)
     */
    private function matchDniDigito(?string $valor, string $ultimoDigito, string $ultimosDosDigitos): bool
    {
        if (!$valor) {
            return false;
        }

        $valor = trim($valor);

        // Rango con guion: "0-1", "8-9", "00-01"
        if (str_contains($valor, '-')) {
            $partes = explode('-', $valor);
            if (count($partes) !== 2) {
                return false;
            }

            $inicio = trim($partes[0]);
            $fin = trim($partes[1]);

            // Si los valores tienen 2 dígitos, comparar con los últimos 2
            if (strlen($inicio) === 2 || strlen($fin) === 2) {
                $inicioNum = (int) $inicio;
                $finNum = (int) $fin;
                $dniNum = (int) $ultimosDosDigitos;
                return $dniNum >= $inicioNum && $dniNum <= $finNum;
            }

            // 1 dígito: comparar con el último
            $inicioNum = (int) $inicio;
            $finNum = (int) $fin;
            $dniNum = (int) $ultimoDigito;
            return $dniNum >= $inicioNum && $dniNum <= $finNum;
        }

        // Valor único: "3", "7"
        $valorLimpio = trim($valor);
        if (strlen($valorLimpio) === 2) {
            return $valorLimpio === $ultimosDosDigitos;
        }

        return $valorLimpio === $ultimoDigito;
    }
}
