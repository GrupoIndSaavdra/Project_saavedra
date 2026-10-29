<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\HasCursorPagination;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use HasCursorPagination;

    // This controller handles user-related actions such as creating, storing, and recovering passwords.
    public function __construct(){
        $this->middleware('auth');
    }
    public function getLayout(){
        $profile = auth()->user()->perfil;
        $layout = [
            1 => "layouts.menu.appAdmin",
            2 => "layouts.menu.appProduction",
            3 => "layouts.menu.appMaster",
            4 => "layouts.menu.appQuality",
            5 => "layouts.menu.appWarehouse",
            6 => "layouts.menu.appAdmin",
        ];
        return $layout[$profile] ?? "layouts.menu.appAdmin";
    }
    public function show(HttpRequest $request){
        $layout = $this->getLayout();
        $query = User::query()->select([
            'id', 'matricula', 'nombre', 'a_paterno', 'a_materno',
            'perfil', 'turno', 'planta', 'area', 'puesto',
            'estatus', 'created_at'
        ]);

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($w) use ($q) {
                $w->where('matricula', 'like', "%{$q}%")
                  ->orWhere('nombre', 'like', "%{$q}%")
                  ->orWhere('a_paterno', 'like', "%{$q}%")
                  ->orWhere('a_materno', 'like', "%{$q}%");
            });
        }

        if ($request->filled('rol') && $request->rol !== 'todos') {
            $query->where('perfil', '=', $request->rol, 'and');
        }

        if ($request->filled('estatus') && $request->estatus !== 'todos') {
            $query->where('estatus', '=', $request->estatus === 'Activo' ? 1 : 0, 'and');
        }

        $users = $query->orderBy('nombre')->orderBy('a_paterno')->orderByDesc('id')->get();
        $totalUsers = User::count();
        return view("users_views.users", compact("layout", "users", "totalUsers"));
    }
    public function organigrama(HttpRequest $request){
        $layout = $this->getLayout();
        $users = User::all();
        return view("users_views.organigrama", compact("layout", "users"));
    }
    public function create(){
        $layout = auth()->user() && ($this->getLayout() == "layouts.appMaster" || $this->getLayout() == "layouts.appAdmin") ? $this->getLayout() : 'layouts.defaultLayout';
        return view("users_views.create_user", compact("layout"));
    }
        /**
     * @param CreateUserRequest $request
     */
    public function store(CreateUserRequest $request){
        $user = User::create($request->validated());
        return redirect()->route('createUser')->with('success', 'Usuario registrado correctamente');
    }

    /**
     * @param HttpRequest $request
     * @param int|string $id
     */
    public function altaUsuario(HttpRequest $request, $id){
        try {
            DB::beginTransaction();
            $user = User::findOrFail($id);
            $user->estatus = 1;
            $user->save();
            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario marcado como activo.',
                    'id'      => $user->id,
                    'estatus' => 1,
                ]);
            }

            return redirect()->back()->with('success', 'Usuario marcado como activo.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al activar usuario.', [
                'user_id' => $id,
                'error'   => $e->getMessage(),
            ]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error al activar el usuario.'], 500);
            }
            return redirect()->back()->with('error', 'Error al activar el usuario.');
        }
    }

    /**
     * @param HttpRequest $request
     * @param int|string $id
     */
    public function bajaUsuario(HttpRequest $request, $id){
        try {
            DB::beginTransaction();
            $user = User::findOrFail($id);
            $user->estatus = 0;
            $user->save();
            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario marcado como inactivo.',
                    'id'      => $user->id,
                    'estatus' => 0,
                ]);
            }

            return redirect()->back()->with('success', 'Usuario marcado como inactivo.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al inactivar usuario.', [
                'user_id' => $id,
                'error'   => $e->getMessage(),
            ]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error al inactivar el usuario.'], 500);
            }
            return redirect()->back()->with('error', 'Error al inactivar el usuario.');
        }
    }

    /**
     * @param HttpRequest $request
     * @param int|string $id
     */
    public function eliminarUsuario(HttpRequest $request, $id){
        try {
            DB::beginTransaction();
            $user = User::findOrFail($id);
            if(auth()->check() && auth()->user()->id == $user->id) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'No puedes eliminar tu propio usuario.'], 403);
                }
                return redirect()->back()->with('error', 'No puedes eliminar tu propio usuario.');
            }
            $user->delete();
            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario eliminado correctamente.',
                    'id'      => (int)$id,
                ]);
            }

            return redirect()->back()->with('success', 'Usuario eliminado correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Fallo de integridad al eliminar usuario.', [
                'user_id' => $id,
                'error'   => $e->getMessage(),
            ]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el usuario porque tiene registros asociados (OTs, piezas o inspecciones).'
                ], 422);
            }
            return redirect()->back()->with('error', 'No se puede eliminar el usuario porque tiene registros asociados (o error de base de datos).');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error inesperado al eliminar usuario.', [
                'user_id' => $id,
                'error'   => $e->getMessage(),
            ]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error interno al procesar la eliminación.'], 500);
            }
            return redirect()->back()->with('error', 'Error interno al procesar la eliminación.');
        }
    }

    /**
     * @param HttpRequest $request
     * @param int|string $id
     */
    public function updateUsuario(HttpRequest $request, $id){
        try {
            DB::beginTransaction();
            $user = User::findOrFail($id);
            $data = $request->validate([
                'nombre' => 'required|string',
                'a_paterno' => 'required|string',
                'a_materno' => 'required|string',
                'perfil' => 'required',
                'turno' => 'nullable|string',
                'planta' => 'nullable|string',
                'area' => 'nullable|string',
                'puesto' => 'nullable|string',
                'contrasena' => 'nullable|string|min:8',
            ]);

            if (!empty($data['contrasena'])) {
                $data['contrasena'] = bcrypt($data['contrasena']);
            } else {
                unset($data['contrasena']);
            }

            $user->update($data);
            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario actualizado correctamente.',
                    'user'    => [
                        'id'              => $user->id,
                        'matricula'       => $user->matricula,
                        'nombre'          => $user->nombre,
                        'a_paterno'       => $user->a_paterno,
                        'a_materno'       => $user->a_materno,
                        'nombre_completo' => $user->name,
                        'perfil'          => (string)$user->perfil,
                        'planta'          => $user->planta ?? '—',
                        'turno'           => $user->turno ?? '—',
                        'area'            => $user->area ?? '—',
                        'puesto'          => $user->puesto ?? '—',
                    ]
                ]);
            }

            return redirect()->back()->with('success', 'Usuario actualizado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al actualizar usuario.', [
                'user_id' => $id,
                'error'   => $e->getMessage(),
            ]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error al actualizar usuario.'], 500);
            }
            return redirect()->back()->with('error', 'Error al actualizar usuario.');
        }
    }
    public function showRecoverPassword(){
        return view('users_views.recoverPassword');
    }
        /**
     * @param HttpRequest $request
     */
    public function recoverPassword(HttpRequest $request){
        $request->validate([
            'matricula' => 'required',
            'nueva_contraseña' => ['required', 'string', 'min:8', 'confirmed']
        ]);
        $user = User::query()->where('matricula', $request->matricula)->first();
        if(!$user){
            return redirect()->to('recoverPassword')->withErrors('Matricula no encontrada.');
        }
        $user->update(['contrasena' => bcrypt($request->nueva_contraseña)]);
        return redirect()->route('recoverPassword')->with('success', 'Contraseña actualizada.');
    }
}