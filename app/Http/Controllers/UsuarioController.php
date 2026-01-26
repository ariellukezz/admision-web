<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;


class UsuarioController extends Controller
{

    public function index()
    {
        $usuarios = User::paginate(10);
        return Inertia::render('Usuarios/index', ['usuarios' => $usuarios]);
    }

    public function create()
    {
        $roles = Role::pluck('name','name')->all();
        return Inertia::render('Usuarios/crear', ['usuarios' => $usuarios]);
    }

    public function store(Request $request)
    {
        if(!$request->id){
            $this->validate($request,[
                'name'=>'required',
                'email'=>'required|email|unique:users,email',
                'password' => 'required|same:confirm-password',
                'roles' => 'required'
            ]);

            $input = $request->all();
            $input['password'] = Hash::make($input['password']);

            $user = User::create($input);
            $user->assignRole($request->roles);

        }else{
            $usuario = User::find($request->id);
            $usuario->name = $request->name;
            $usuario->id_proceso = $request->id_proceso;
            if(isset($request->password)){
                $usuario->password = Hash::make($request->password);
            }
            $usuario->materno = $request->materno;
            $usuario->paterno = $request->paterno;
            $usuario->save();
        }

        return redirect()->route('usuarios.index');

    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();

        return Inertia::render('Usuarios/editar', [
            'user' => $user,
            'roles' => $roles,
            'userRole' => $userRole,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $this->validate($request,[
            'name'=>'required',
            'email'=>'required|email|unique:users,email'.$id,
            'password'=>'same:confirm-password',
            'roles'=>'required'
        ]);

        $input = $request->all();
        if(!empty($input['password'])){
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = Arr::excep($input, array('password'));
        }

        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id', $id)->delete();

        $user->assignRole($request->roles);
        return redirect()->route('usuarios.index');

    }

    public function destroy(string $id)
    {
        User::find($id)->delete();
        return redirect()->route('usuarios.index');
    }

    public function getRoles()
    {
        $roles = DB::select('SELECT id, name AS value FROM roles;');

        $this->response['datos'] = $roles;
        return response()->json($this->response, 200);

    }

    public function getUsuarios(Request $request){

        $res = User::select( 'users.id', 'users.dni', 'users.name', 'users.paterno', 'users.materno', 'users.email', 'users.celular',
        'roles.name AS rol', 'users.id_rol', 'id_proceso', 'procesos.nombre AS proceso', 'users.estado')
        ->join('roles','roles.id','users.id_rol')
        ->join('procesos','procesos.id','users.id_proceso')
        ->where(function ($query) use ($request) {
            return $query
                ->orWhere('users.name', 'LIKE', '%' . $request->term . '%')
                ->orWhere(DB::raw("CONCAT(users.name,' ',users.paterno,' ',users.materno)"),'LIKE', '%' . $request->term . '%')
                ->orWhere('users.email', 'LIKE', '%' . $request->term . '%');
        })->orderBy('users.id', 'DESC')
        ->get();

        $this->response['usuarios'] = $res;
        return response()->json($this->response, 200);
    }


    public function saveUsuario(Request $request) {

        if (!$request->id) {
            $user = new User();
            $user->dni = $request->dni;
            $user->name = $request->name;
            $user->paterno = $request->paterno;
            $user->materno = $request->materno;
            $user->email = $request->email;
            $user->celular = $request->celular;
            $user->password = Hash::make($request->password);
            $user->id_rol = $request->id_rol;
            $user->estado = $request->estado;
            $user->id_proceso = $request->id_proceso;
            $user->id_usuario = auth()->id();
            $user->save();
        } else {

            $usuario = User::find($request->id);
            if (!$usuario) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }
            $usuario->dni = $request->dni;
            $usuario->name = $request->name;
            $usuario->paterno = $request->paterno;
            $usuario->materno = $request->materno;
            $usuario->email = $request->email;
            $usuario->celular = $request->celular;
            $usuario->estado = $request->estado;
            $usuario->id_rol = $request->id_rol;
            $usuario->id_proceso = $request->id_proceso;
            if ($request->has('password') ) {
                $usuario->password = Hash::make($request->password);
            }
            $usuario->save();
        }

        return response()->json(['message' => 'Usuario guardado correctamente'], 200);
    }

    public function getPermisos(){
        $res = DB::select('SELECT permissions.id, permissions.name, pa
        FROM users
        JOIN roles ON users.id_rol = roles.id
        JOIN role_has_permissions ON role_has_permissions.role_id = roles.id
        JOIN permissions ON role_has_permissions.permission_id = permissions.id
        WHERE users.id = '. auth()->id());
        $this->response['permisos'] = $res;
        return response()->json($this->response, 200);
    }

    public function cambiarContra(Request $request){

        $usuario = User::find(auth()->id());

        if (!Hash::check($request->oldPassword, $usuario->password)) {
            return response()->json(['mensaje' => 'La contraseña actual no es correcta.'], 400);
        }

        $usuario->password = Hash::make($request->newPassword);
        $usuario->save();

        $this->response['estado'] = true;
        $this->response['datos'] = $usuario;
        return response()->json($this->response, 200);
    }


}
