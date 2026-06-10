<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function share(Request $request)
    {
        $user = $request->user();
        $procesoActual = null;

        if ($user && $user->id_proceso) {
            $proceso = \App\Models\Proceso::find($user->id_proceso);
            if ($proceso) {
                $procesoActual = [
                    'id' => $proceso->id,
                    'nombre' => $proceso->nombre,
                    'anio' => $proceso->anio,
                ];
            }
        }

        $notificacionesNoLeidas = 0;

        if ($user && $user->id_rol == 2) {
            $notificacionesNoLeidas = $user->unreadNotifications()->count();
        }

        $permisos = [];

        if ($user) {
            $permisos = $user->getAllPermissions()->toArray();
        }

        $userData = null;
        if ($user) {
            $userData = $user->toArray();
            // Asegurar que la foto tenga una ruta absoluta
            if (!empty($userData['foto']) && !str_starts_with($userData['foto'], 'http') && !str_starts_with($userData['foto'], '/')) {
                $userData['foto'] = '/' . $userData['foto'];
            }
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $userData,
                'permissions' => $permisos,
            ],
            'proceso_actual' => $procesoActual,
            'flash' => function () use ($request) {
                return [
                    'success' => $request->session()->get('success'),
                ];
            },
            'showingMobileMenu' => false,
            'notificacionesNoLeidas' => $notificacionesNoLeidas,
        ]);
    }
}
