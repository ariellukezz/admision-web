<?php
namespace App\Observers;
use DB;

class GlobalObserver
{
    public function created($model)
    {
        $clientIp = request()->ip();
        DB::table('activity_logs')->insert([
            'action' => 'insertó',
            'model' => get_class($model),
            'tabla' => $model->getTable(),
            'model_id' => $model->id,
            'user_id' => auth()->user()->id,
            'direccion' => $clientIp,
            'fecha' => now()
        
        ]);

    }

    public function updated($model)
    {
        $clientIp = request()->ip();
        DB::table('activity_logs')->insert([
            'action' => 'actualizó',
            'model' => get_class($model),
            'tabla' => $model->getTable(),
            'model_id' => $model->id,
            'user_id' => auth()->id() ?? null,
            'direccion' => $clientIp,
            'fecha' => now()
        ]);
    }

    public function deleted($model) {
        $clientIp = request()->ip();                
        DB::table('activity_logs')->insert([
            'action' => 'eliminó',
            'model' => get_class($model),
            'tabla' => $model->getTable(),
            'model_id' => $model->id,
            'user_id' => auth()->id() ?? null,
            'direccion' => $clientIp,
            'fecha' => now()
        ]);
    }

}