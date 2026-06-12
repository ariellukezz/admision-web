<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('rol')->get();
        return response()->json($users);
    }

    // Detalle de usuario
    public function show($id)
    {
        $user = User::with('rol')->findOrFail($id);
        return response()->json($user);
    }
}


