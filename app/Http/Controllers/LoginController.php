<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller {

    public function login(Request $request){

        $rules = [
            'email' => 'required|string|email|max:100',
            'password' => 'required|string'
        ];

        $validator = \Validator::make($request->input(), $rules);
        if($validator->fails()){
            return response()->json([
                'status' => false, 
                'errors' => $validator->errors()->all()
            ], 400);
        }
        if(!Auth::attempt($request->only('email','password'))){
            return response()->json([
                'status' => false,
                'errors' => ['Unauthorized']
            ],401);
        }
        $user = User::where('email',$request->email)->first();
        return response()->json([
            'status' => true,
            'message' => 'User logged in succesfully',
            'data' => $user,
            'token' => $user->createToken('API TOKEN')->plainTextToken
        ], 200);

    }

    public function getCodigoConexion($codigoConexion){

        $usuario = User::where('codigo_conexion', $codigoConexion)->first();

        if (!$usuario) {    
            return response()->json([
                'status' => false,
                'message' => 'Código de conexión inválido'
            ], 404);
        }
        
        return response()->json([
            'status' => true,
            'token_conexion' => $usuario->token_conexion,
            'id_usuario' => $usuario->id,
            'nombres' => $usuario->name,
            'paterno' => $usuario->paterno.' '.$usuario->materno,
            'id_proceso' => $usuario->id_proceso,
            'message' => 'Código de conexión válido',

        ], 200);
    }


    public function validarConexion(Request $request)
    {
        return response()->json([
            'status' => (bool) User::where('token_conexion', $request->token_conexion)->exists()
        ]);
    }


    public function logout(){
        auth()->user()->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully'
        ],200);
    }


}
