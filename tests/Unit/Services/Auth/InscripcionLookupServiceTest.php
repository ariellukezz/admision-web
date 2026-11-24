<?php

namespace Tests\Unit\Services\Auth;

use App\Services\Auth\InscripcionLookupService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class InscripcionLookupServiceTest extends TestCase
{
    public function test_builds_lookup_for_the_requested_process(): void
    {
        $idProceso = 2026;
        $documents = ['61279694'];
        $results = collect([
            (object) [
                'nro_doc' => '61279694',
                'nombre' => 'Proceso de admisión',
                'estado' => 'INSCRITO',
            ],
        ]);
        $query = Mockery::mock(Builder::class);

        DB::shouldReceive('table')
            ->once()
            ->with('inscripciones as ins')
            ->andReturn($query);

        $query->shouldReceive('join')
            ->once()
            ->with('postulante as pos', 'ins.id_postulante', '=', 'pos.id')
            ->andReturnSelf();
        $query->shouldReceive('join')
            ->once()
            ->with('procesos as pro', 'ins.id_proceso', '=', 'pro.id')
            ->andReturnSelf();
        $query->shouldReceive('where')
            ->once()
            ->with('ins.id_proceso', $idProceso)
            ->andReturnSelf();
        $query->shouldReceive('where')
            ->once()
            ->with('ins.estado', 0)
            ->andReturnSelf();
        $query->shouldReceive('whereIn')
            ->once()
            ->with('pos.nro_doc', $documents)
            ->andReturnSelf();
        $query->shouldReceive('select')
            ->once()
            ->with('pos.nro_doc', 'pro.nombre')
            ->andReturnSelf();
        $query->shouldReceive('selectRaw')
            ->once()
            ->with("CASE WHEN ins.estado = 0 THEN 'INSCRITO' ELSE 'OBSERVADO' END AS estado")
            ->andReturnSelf();
        $query->shouldReceive('orderBy')
            ->once()
            ->with('pro.nombre', 'asc')
            ->andReturnSelf();
        $query->shouldReceive('get')
            ->once()
            ->andReturn($results);

        $response = app(InscripcionLookupService::class)->buscarInscritos($idProceso, $documents);

        $this->assertInstanceOf(Collection::class, $response);
        $this->assertSame($results, $response);
    }
}
