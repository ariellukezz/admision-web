<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

#[OpenApi\PathItem]
class UserController extends Controller
{
    #[OpenApi\Operation(tags: ['Usuarios'], method: 'GET', summary: 'Listar usuarios')]
    #[OpenApi\Parameters([
        new OpenApi\Parameter(name: 'buscar', in: 'query', required: false, schema: new OpenApi\Schema(type: 'string')),
        new OpenApi\Parameter(name: 'page', in: 'query', required: false, schema: new OpenApi\Schema(type: 'integer')),
        new OpenApi\Parameter(name: 'pageSize', in: 'query', required: false, schema: new OpenApi\Schema(type: 'integer')),
    ])]
    #[OpenApi\Response(status: 200, description: 'Lista paginada de usuarios')]

    public function index(Request $request): JsonResponse
    {
        try {
            $buscar = $request->query('buscar');
            $pageSize = (int) $request->query('pageSize', 10);
            $page = (int) $request->query('page', 1);

            $query = User::query()
                ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                ->select('users.*', 'roles.nombre as rol_nombre');

            if ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombres', 'LIKE', "%{$buscar}%")
                    ->orWhere('email', 'LIKE', "%{$buscar}%");
                });
            }

            $users = $query->paginate($pageSize, ['*'], 'page', $page);

            // Firmar URLs de las fotos (si existen)
            $users->getCollection()->transform(function ($user) {
                if ($user->foto && Storage::disk('local')->exists($user->foto)) {
                    $user->foto_url = Storage::disk('local')->temporaryUrl(
                        $user->foto,
                        now()->addMinutes(1) // URL válida por 1 minuto
                    );
                } else {
                    $user->foto_url = null;
                }
                return $user;
            });

            return response()->json($users, 200);

        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }  

    #[OpenApi\Operation(tags: ['Usuarios'], method: 'POST', summary: 'Crear un nuevo usuario')]
    #[OpenApi\RequestBody(
        content: new OpenApi\JsonContent(
            required: ['nombres','email','password'],
            properties: [
                'nombres' => new OpenApi\Schema(type: 'string'),
                'paterno' => new OpenApi\Schema(type: 'string', nullable: true),
                'materno' => new OpenApi\Schema(type: 'string', nullable: true),
                'email' => new OpenApi\Schema(type: 'string', format: 'email'),
                'celular' => new OpenApi\Schema(type: 'string', nullable: true),
                'foto' => new OpenApi\Schema(type: 'string', nullable: true),
                'password' => new OpenApi\Schema(type: 'string', format: 'password'),
            ]
        )
    )]
    #[OpenApi\Response(status: 201, description: 'Usuario creado')]
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'dni'     => 'required|string|max:8|unique:users,dni',
                'nombres' => 'required|string|max:255',
                'paterno' => 'required|string|max:255',
                'materno' => 'required|string|max:255',
                'celular' => 'nullable|string|max:15',
                'email'   => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'role_id' => 'required|integer|exists:roles,id',
                'estado'  => 'required|integer|in:0,1',
                'foto'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors'  => $validator->errors()
                ], 422);
            }

            // Procesar la foto si existe
            $fotoPath = null;
            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                $fotoPath = $request->file('foto')->store('usuarios/fotos', 'public');
            }

            $user = User::create([
                'dni'      => $request->dni,
                'nombres'  => $request->nombres,
                'paterno'  => $request->paterno,
                'materno'  => $request->materno,
                'celular'  => $request->celular,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role_id'  => $request->role_id,
                'estado'   => $request->estado,
                'foto'     => $fotoPath,
            ]);
            $user->foto_url = $fotoPath ? asset('storage/' . $fotoPath) : null;

            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'user'    => $user,
            ], 201);

        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Usuarios'], method: 'GET', summary: 'Obtener usuario por ID')]
    #[OpenApi\Response(status: 200, description: 'Usuario encontrado')]
    #[OpenApi\Response(status: 404, description: 'Usuario no encontrado')]
    public function show($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            return response()->json($user, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Usuarios'], method: 'PUT', summary: 'Actualizar un usuario')]
    #[OpenApi\Response(status: 200, description: 'Usuario actualizado')]
    #[OpenApi\Response(status: 404, description: 'Usuario no encontrado')]
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'dni' => 'required|string|max:8|unique:users,dni,' . $user->id,
                'nombres' => 'required|string|max:255',
                'paterno' => 'required|string|max:255',
                'materno' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'celular' => 'nullable|string|max:20',
                'role_id' => 'required|integer|exists:roles,id',
                'estado' => 'required|integer|in:0,1',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'password' => 'nullable|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();
            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                if ($user->foto) {
                    Storage::disk('public')->delete($user->foto);
                }
                $validated['foto'] = $request->file('foto')->store('usuarios/fotos', 'public');
            }

            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
                unset($validated['password_confirmation']);
            }

            $user->update($validated);
            return response()->json([
                'message' => 'Usuario actualizado exitosamente',
                'user' => $user
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Usuarios'], method: 'DELETE', summary: 'Eliminar usuario')]
    #[OpenApi\Response(status: 204, description: 'Usuario eliminado')]
    #[OpenApi\Response(status: 404, description: 'Usuario no encontrado')]
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }            
            $user->delete();

            return response()->json([
                'message' => 'Usuario eliminado exitosamente'
            ], 200);
            
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    protected function errorResponse(Throwable $e, int $status = 500): JsonResponse
    {
        $response = ['message' => 'Error interno del servidor'];

        if (config('app.debug')) {
            $response['error'] = $e->getMessage();
            $response['trace'] = $e->getTrace();
        }

        return response()->json($response, $status);
    }
}
