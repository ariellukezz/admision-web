<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function getPreinscripcionEmailVerification()
    {
        $value = Setting::get('preinscripcion_email_verification', '1');

        return response()->json([
            'estado' => $value === '1',
        ]);
    }

    public function togglePreinscripcionEmailVerification(Request $request)
    {
        $current = Setting::get('preinscripcion_email_verification', '1');
        $newValue = $current === '1' ? '0' : '1';

        Setting::set('preinscripcion_email_verification', $newValue);

        return response()->json([
            'estado'  => true,
            'mensaje' => $newValue === '1'
                ? 'Verificación por correo activada'
                : 'Verificación por correo desactivada',
            'valor'   => $newValue === '1',
        ]);
    }
}
