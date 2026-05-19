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

        // $user = User::where('token_conexion', $codigoConexion)->first();
        // if (!$user) {
        //     return response()->json([
        //         'status' => false,
        //         'errors' => ['Invalid connection code']
        //     ], 404);
        // }

        return response()->json([
            'status' => true,
            'codigo_conexion' => '8hIcptrxisoqUtpvU1YZa9eYaZgViuS0LqI0pq6RfmT3O1QJnyAqzwKQNjzr'
        ], 200);
    }

    public function logout(){
        auth()->user()->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully'
        ],200);
    }


}
