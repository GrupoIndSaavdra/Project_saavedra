<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Perfil;
use App\Models\Puesto;
use App\Models\User;
use App\Traits\HasCursorPagination;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use HasCursorPagination;

    // This controller handles user-related actions such as creating, storing, and recovering passwords.
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getLayout()
    {
        $profile = (string) (auth()->user()->perfil ?? '9');
        $layout = [
            '1' => 'layouts.appMenu',
            '2' => 'layouts.appMenu',
            '3' => 'layouts.appMenu',
            '4' => 'layouts.appMenu',
            '5' => 'layouts.appMenu',
            '6' => 'layouts.appMenu',
            '7' => 'layouts.appMenu',
            '8' => 'layouts.appMenu',
            '9' => 'layouts.appMenu',
        ];

        return $layout[$profile] ?? 'layouts.appMenu';
    }

    public function show(HttpRequest $request)
    {
        $this->asegurarCatalogos();
        $layout = $this->getLayout();
        $query = User::query()->with(['puestoInfo'])->select([
            'id',
            'matricula',
            'nombre',
            'a_paterno',
            'a_materno',
            'perfil',
            'perfil_id',
            'turno',
            'planta',
            'puesto_id',
            'estatus',
            'created_at',
        ]);

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($w) use ($q) {
                $w->where('matricula', 'like', "%{$q}%")
                    ->orWhere('nombre', 'like', "%{$q}%")
                    ->orWhere('a_paterno', 'like', "%{$q}%")
                    ->orWhere('a_materno', 'like', "%{$q}%")
                    ->orWhereHas('puestoInfo', fn($p) => $p->where('nombre', 'like', "%{$q}%"));
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

        $perfiles = Perfil::orderBy('id')->get();
        $puestos = Puesto::orderBy('codigo_nivel')->get();

        return view('users_views.users', compact('layout', 'users', 'totalUsers', 'perfiles', 'puestos'));
    }

    public function organigrama(HttpRequest $request)
    {
        $layout = $this->getLayout();
        $users = User::with(['puestoInfo', 'perfilInfo'])->get();

        return view('users_views.organigrama', compact('layout', 'users'));
    }

    public function create()
    {
        $this->asegurarCatalogos();
        $layout = auth()->user() && ($this->getLayout() == 'layouts.appMaster' || $this->getLayout() == 'layouts.appAdmin') ? $this->getLayout() : 'layouts.defaultLayout';

        $perfiles = Perfil::orderBy('id')->get();
        $puestos = Puesto::orderBy('codigo_nivel')->get();

        return view('users_views.create_user', compact('layout', 'perfiles', 'puestos'));
    }

    private function asegurarCatalogos(): void
    {
        try {
            if (Perfil::count() === 0 || Puesto::count() === 0) {
                \Illuminate\Support\Facades\Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\PerfilesPuestosSeeder',
                    '--force' => true,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo auto-sembrar catálogos de perfiles y puestos.', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function store(CreateUserRequest $request)
    {
        $validated = $request->validated();
        $validated['perfil'] = (string) $validated['perfil'];
        $validated['perfil_id'] = (int) $validated['perfil'];

        $user = User::create($validated);

        return redirect()->route('createUser')->with('success', "Usuario {$user->nombre} {$user->a_paterno} registrado correctamente con matrícula {$user->matricula}.");
    }

    /**
     * @param  int|string  $id
     */
    public function altaUsuario(HttpRequest $request, $id)
    {
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
                    'id' => $user->id,
                    'estatus' => 1,
                ]);
            }

            return redirect()->back()->with('success', 'Usuario marcado como activo.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al activar usuario.', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error al activar el usuario.'], 500);
            }

            return redirect()->back()->with('error', 'Error al activar el usuario.');
        }
    }

    /**
     * @param  int|string  $id
     */
    public function bajaUsuario(HttpRequest $request, $id)
    {
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
                    'id' => $user->id,
                    'estatus' => 0,
                ]);
            }

            return redirect()->back()->with('success', 'Usuario marcado como inactivo.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al inactivar usuario.', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error al inactivar el usuario.'], 500);
            }

            return redirect()->back()->with('error', 'Error al inactivar el usuario.');
        }
    }

    /**
     * @param  int|string  $id
     */
    public function eliminarUsuario(HttpRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $user = User::findOrFail($id);
            if (auth()->check() && auth()->user()->id == $user->id) {
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
                    'id' => (int) $id,
                ]);
            }

            return redirect()->back()->with('success', 'Usuario eliminado correctamente.');
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('Fallo de integridad al eliminar usuario.', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el usuario porque tiene registros asociados (OTs, piezas o inspecciones).',
                ], 422);
            }

            return redirect()->back()->with('error', 'No se puede eliminar el usuario porque tiene registros asociados (o error de base de datos).');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error inesperado al eliminar usuario.', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error interno al procesar la eliminación.'], 500);
            }

            return redirect()->back()->with('error', 'Error interno al procesar la eliminación.');
        }
    }

    /**
     * @param  int|string  $id
     */
    public function updateUsuario(UpdateUserRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $user = User::findOrFail($id);
            $data = $request->validated();

            $data['perfil'] = (string) $data['perfil'];
            $data['perfil_id'] = (int) $data['perfil'];

            if (!empty($data['contrasena'])) {
                $data['contrasena'] = bcrypt($data['contrasena']);
            } else {
                unset($data['contrasena']);
            }

            $user->update($data);
            DB::commit();

            // Recargar relaciones para respuesta precisa
            $user->load(['puestoInfo', 'perfilInfo']);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Usuario actualizado correctamente.',
                    'user' => [
                        'id' => $user->id,
                        'matricula' => $user->matricula,
                        'nombre' => $user->nombre,
                        'a_paterno' => $user->a_paterno,
                        'a_materno' => $user->a_materno,
                        'nombre_completo' => $user->name,
                        'perfil' => (string) $user->perfil,
                        'perfil_id' => $user->perfil_id,
                        'planta' => $user->planta_label ?: ($user->planta ?? '—'),
                        'turno' => $user->turno ?? '—',
                        'puesto' => $user->puesto ?? '—',
                        'puesto_id' => $user->puesto_id,
                    ],
                ]);
            }

            return redirect()->back()->with('success', 'Usuario actualizado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al actualizar usuario.', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error al actualizar usuario.'], 500);
            }

            return redirect()->back()->with('error', 'Error al actualizar usuario.');
        }
    }

    public function showRecoverPassword()
    {
        return view('users_views.recoverPassword');
    }

    public function recoverPassword(HttpRequest $request)
    {
        $request->validate([
            'matricula' => 'required',
            'nueva_contraseña' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $user = User::query()->where('matricula', $request->matricula)->first();
        if (!$user) {
            return redirect()->to('recoverPassword')->withErrors('Matricula no encontrada.');
        }
        $user->update(['contrasena' => bcrypt($request->nueva_contraseña)]);

        return redirect()->route('recoverPassword')->with('success', 'Contraseña actualizada.');
    }
}
