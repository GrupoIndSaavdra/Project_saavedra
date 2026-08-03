<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;

class UserController extends Controller
{
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
        ];
        return $layout[$profile];
    }
    public function show(){
        $layout = $this->getLayout();
        $users = User::all();
        return view("users_views.users", compact("layout", "users"));
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
        return redirect()->route('create_user')->with('success', 'Usuario registrado correctamente');
    }

    /**
     * @param HttpRequest $request
     * @param int|string $id
     */
    public function altaUsuario(HttpRequest $request, $id){
        $user = User::findOrFail($id);
        $user->estatus = 1;
        $user->save();
        return redirect()->back()->with('success', 'Usuario marcado como activo.');
    }

    /**
     * @param HttpRequest $request
     * @param int|string $id
     */
    public function bajaUsuario(HttpRequest $request, $id){
        $user = User::findOrFail($id);
        $user->estatus = 0;
        $user->save();
        return redirect()->back()->with('success', 'Usuario marcado como inactivo.');
    }

    /**
     * @param HttpRequest $request
     * @param int|string $id
     */
    public function eliminarUsuario(HttpRequest $request, $id){
        try {
            $user = User::findOrFail($id);
            if(auth()->check() && auth()->user()->id == $user->id) {
                return redirect()->back()->with('error', 'No puedes eliminar tu propio usuario.');
            }
            $user->delete();
            return redirect()->back()->with('success', 'Usuario eliminado correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('error', 'No se puede eliminar el usuario porque tiene registros asociados (o error de base de datos).');
        }
    }

    /**
     * @param HttpRequest $request
     * @param int|string $id
     */
    public function updateUsuario(HttpRequest $request, $id){
        $user = User::findOrFail($id);
        $data = $request->validate([
            'nombre' => 'required',
            'a_paterno' => 'required',
            'a_materno' => 'required',
            'perfil' => 'required'
        ]);
        $user->update($data);
        return redirect()->back()->with('success', 'Usuario actualizado correctamente.');
    }
    public function showRecoverPassword(){
        return view('users_views.recover_password');
    }
        /**
     * @param HttpRequest $request
     */
    public function recover_password(HttpRequest $request){
        $request->validate([
            'matricula' => 'required',
            'nueva_contraseña' => ['required', 'string', 'min:8', 'confirmed']
        ]);
        $user = User::query()->where('matricula', $request->matricula)->first();
        if(!$user){
            return redirect()->to('recover_password')->withErrors('Matricula no encontrada.');
        }
        $user->update(['contrasena' => bcrypt($request->nueva_contraseña)]);
        return redirect()->route('recover_password')->with('success', 'Contraseña actualizada.');
    }
}