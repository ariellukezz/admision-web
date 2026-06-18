<?php

namespace App\Http\Controllers;

use App\Services\Revisor\RevisorDashboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class RevisorDashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private RevisorDashboardService $service,
    ) {}

    public function resumen()
    {
        $proceso = auth()->user()->id_proceso;
        return $this->success($this->service->resumen($proceso));
    }

    public function inscripcionesPorPrograma()
    {
        $proceso = auth()->user()->id_proceso;
        return $this->success($this->service->inscripcionesPorPrograma($proceso));
    }

    public function inscripcionesPorArea()
    {
        $proceso = auth()->user()->id_proceso;
        return $this->success($this->service->inscripcionesPorArea($proceso));
    }

    public function generoPorArea()
    {
        $proceso = auth()->user()->id_proceso;
        return $this->success($this->service->generoPorArea($proceso));
    }

    public function timelineInscripciones()
    {
        $proceso = auth()->user()->id_proceso;
        return $this->success($this->service->timelineInscripciones($proceso));
    }

    public function biometricoResumen()
    {
        $proceso = auth()->user()->id_proceso;
        return $this->success($this->service->biometricoResumen($proceso));
    }

    public function modalidadDistribucion()
    {
        $proceso = auth()->user()->id_proceso;
        return $this->success($this->service->modalidadDistribucion($proceso));
    }

    public function verificacionesPendientes()
    {
        $proceso = auth()->user()->id_proceso;
        return $this->success($this->service->verificacionesPendientes($proceso));
    }
}
