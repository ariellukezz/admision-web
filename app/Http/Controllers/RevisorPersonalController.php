<?php

namespace App\Http\Controllers;

use App\Services\Revisor\RevisorPersonalService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class RevisorPersonalController extends Controller
{
    use ApiResponse;

    public function __construct(
        private RevisorPersonalService $service,
    ) {}

    public function resumen()
    {
        $userId = auth()->id();
        $proceso = auth()->user()->id_proceso;
        return $this->success($this->service->resumen($userId, $proceso));
    }

    public function timeline()
    {
        $userId = auth()->id();
        $proceso = auth()->user()->id_proceso;
        return $this->success($this->service->timeline($userId, $proceso));
    }

    public function accionesRecientes()
    {
        $userId = auth()->id();
        $proceso = auth()->user()->id_proceso;
        return $this->success($this->service->accionesRecientes($userId, $proceso));
    }

    public function distribucionActividad()
    {
        $userId = auth()->id();
        $proceso = auth()->user()->id_proceso;
        return $this->success($this->service->distribucionActividad($userId, $proceso));
    }

    public function ranking()
    {
        $userId = auth()->id();
        $proceso = auth()->user()->id_proceso;
        return $this->success($this->service->ranking($proceso, $userId));
    }

    public function pendientes()
    {
        $proceso = auth()->user()->id_proceso;
        return $this->success($this->service->pendientes($proceso));
    }
}
