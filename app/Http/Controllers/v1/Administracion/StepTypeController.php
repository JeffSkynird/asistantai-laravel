<?php

namespace App\Http\Controllers\v1\Administracion;

use App\Http\Controllers\Controller;
use App\Models\StepType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \Validator;

class StepTypeController extends Controller
{
    /**
     * Función para crear nuevos steptype
     * @param Request $request 
     * @return json
     */
    public function create(Request $request)
    {
        try {
            $params = $request->all();

            $vacios = Validator::make($request->all(), [
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
            StepType::create($params);
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
     * Función para obtener los datos de un steptype
     * @param int $id 
     * @return json
     */
    public function show($id)
    {
        $data = StepType::find($id);
        return response()->json([
            "status" => "200",
            "message" => 'Datos obtenidos con éxito',
            "data" => $data,
            "type" => 'success'
        ]);
    }
    /**
     * Función para modificar los datos de un steptype
     * @param int $id, Request $request 
     * @return json
     */
    public function update(Request $request, $id)
    {
        $vacios = Validator::make($request->all(), [
            'name' => 'required'
        ]);
        if ($vacios->fails()) {
            return response([
                'message' => "No deje campos vacíos",
                'type' => "error",
            ]);
        }
       
        try {
            StepType::find($id)->update($request->all());
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
     * Función para eliminar un steptype
     * @param  int $id
     * @return json
     */
    public function delete($id)
    {
        $data = StepType::find($id);
        $data->delete();
      
        return response()->json([
            "status" => "200",
            "message" => 'Eliminación exitosa',
            "type" => 'success'
        ]);
    }
    /**
     * Función para obtener todos los steptype
     * @return json
     */
    public function index()
    {
        $data = StepType::all();
        return response()->json([
            "status" => "200",
            "data" => $data,
            "message" => 'Listado exitoso',
            "type" => 'success'
        ]);
    }
}
