<?php

namespace App\Http\Controllers\v1\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Step;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \Validator;

class StepController extends Controller
{
    /**
     * Función para crear nuevos step
     * @param Request $request 
     * @return json
     */
    public function create(Request $request)
    {
        try {
            $params = $request->all();

            $vacios = Validator::make($request->all(), [
                'event_id' => 'required',
                'step_type_id' => 'required',
                'name' => 'required'
            ]);
            if ($vacios->fails()) {
                return response([
                    'message' => "No debe dejar campos vacíos",
                    'fields' => $request->all(),
                    'type' => "error",
                ]);
            }
            $params['user_id']=Auth::id();
            Step::create($params);
            return response()->json([
                "status" => "200",
                "message" => 'Registro exitoso',
                "type" => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "500",
                "message" => $e->getMessage(),
                "type" => 'error'
            ]);
        }
    }
    /**
     * Función para obtener los datos de un step
     * @param int $id 
     * @return json
     */
    public function show($id)
    {
        $data = Step::find($id);
        return response()->json([
            "status" => "200",
            "message" => 'Datos obtenidos con éxito',
            "data" => $data,
            "type" => 'success'
        ]);
    }
    /**
     * Función para modificar los datos de un step
     * @param int $id, Request $request 
     * @return json
     */
    public function update(Request $request, $id)
    {
        $vacios = Validator::make($request->all(), [
            'event_id' => 'required',
            'step_type_id' => 'required',
            'name' => 'required'
        ]);
        if ($vacios->fails()) {
            return response([
                'message' => "No deje campos vacíos",
                'type' => "error",
            ]);
        }
       
        try {
            Step::find($id)->update($request->all());
            return response()->json([
                "status" => "200",
                "message" => 'Modificación exitosa',
                "type" => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "500",
                "message" => $e->getMessage(),
                "type" => 'error'
            ]);
        }
    }
   
    /**
     * Función para eliminar un step
     * @param  int $id
     * @return json
     */
    public function delete($id)
    {
        $data = Step::find($id);
        $data->delete();
      
        return response()->json([
            "status" => "200",
            "message" => 'Eliminación exitosa',
            "type" => 'success'
        ]);
    }
    /**
     * Función para obtener todos los step
     * @return json
     */
    public function index()
    {
        $data = Step::with('stepTypes')->get();
        return response()->json([
            "status" => "200",
            "data" => $data,
            "message" => 'Listado exitoso',
            "type" => 'success'
        ]);
    }

     /**
     * Función para obtener todos los steps de un event
     * @return json
     */
    public function indexByEvent($id)
    {
        $data = Step::with('stepTypes')->where('event_id',$id)->get();
        return response()->json([
            "status" => "200",
            "data" => $data,
            "message" => 'Listado exitoso',
            "type" => 'success'
        ]);
    }
      /**
     * Función para obtener todos los steps de un event abierto
     * @return json
     */
    public function indexByEventOpen(Request $request)
    {
        $isFinal = $request->isFinal;
        $data = Step::with('stepTypes')->whereHas('event', function ($query) use($isFinal){
            $query->where('is_open', true);
            if($isFinal){
                $query->where('is_final', true);
            }
        })->get();
        return response()->json([
            "status" => "200",
            "data" => $data,
            "message" => 'Listado exitoso',
            "type" => 'success'
        ]);
    }
}
