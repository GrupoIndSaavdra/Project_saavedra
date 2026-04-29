<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoldingRequest;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;

class MoldingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function create()
    {
        return view('moldings_views.create_molding');
    }
        /**
     * @param \Illuminate\Http\Request MoldingRequest $request
     */
    public function store(MoldingRequest $request)
    {
        $moldura = Moldura::create($request->all());
        return redirect()->to('createMolding')->with('success', 'Moldura registrada correctamente.');
    }

    public function edit()
    {
        $moldings = Moldura::all();
        return view('moldings_views.edit_molding', compact('moldings'));
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function update(Request $request)
    {
        $molding = Moldura::query()->find($request->moldingId);
        $molding->nombre = $request->moldingName;
        $molding->update();
        return redirect()->to('editMolding')->with('success', 'Moldura actualizada correctamente.');
    }
        /**
     * @param mixed $moldingId
     */
    public function destroy($moldingId)
    {
        $workOrder = Orden_trabajo::query()->where('id_moldura', $moldingId)->first();
        if (!$workOrder) {
            $molding = Moldura::query()->find($moldingId);
            if ($molding) {
                $molding->delete();
                return redirect()->to('editMolding')->with('success', 'Moldura eliminada correctamente.');
            } else {
                return redirect()->to('editMolding')->with('error', 'Moldura no encontrada.');
            }
        } else {
            return redirect()->to('editMolding')->with('error', 'No se puede eliminar la moldura porque está asociada a una orden de trabajo.');
        }
    }
}
