<?php

namespace App\Http\Controllers\v1\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Step;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use \Validator;

class EventController extends Controller
{
    /**
     * Función para crear nuevos event
     * @param Request $request 
     * @return json
    */
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $steps = $request->input('steps');

            $vacios = Validator::make($request->all(), [
                'name' => 'required',
                'start_date' => 'required',
                'end_date' => 'required'
            ]);
            if ($vacios->fails()) {
                return response([
                    'message' => "No debe dejar campos vacíos",
                    'fields' => $request->all(),
                    'type' => "error",
                ]);
            }
            if($params['is_open']==true){
                $count = Event::where('is_open', true)->count();
                if($count>0){
                    $params['is_open']=false;
                }
            }
            $params['user_id']=Auth::id();
            $event = Event::create($params);

            if(count($steps)>0){
                foreach ($steps as $val) {
                    $val['user_id']=Auth::id();
                    $event->steps()->create($val);
                }
            }
            DB::commit();
            return response()->json([
                "status" => "200",
                "message" => 'Registro exitoso',
                "type" => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "status" => "500",
                "message" => $e->getMessage(),
                "type" => 'error'
            ]);
        }
    }
    /**
     * Función para obtener los datos de un event
     * @param int $id 
     * @return json
     */
    public function show($id)
    {
        $data = Event::find($id);
        return response()->json([
            "status" => "200",
            "message" => 'Datos obtenidos con éxito',
            "data" => $data,
            "type" => 'success'
        ]);
    }
    /**
     * Función para modificar los datos de un event
     * @param int $id, Request $request 
     * @return json
     */
    public function update(Request $request, $id)
    {
        $vacios = Validator::make($request->all(), [
            'name' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ]);
        if ($vacios->fails()) {
            return response([
                'message' => "No deje campos vacíos",
                'type' => "error",
            ]);
        }
        DB::beginTransaction();
        try {
            $event = Event::find($id);
            $steps = $request->input('steps');
            if(count($steps)>0){
                foreach ($steps as $val) {
                    if(isset($val['id'])){
                        unset($val['step_type']);
                        unset($val['tableData']);
                        $event->steps()->where('id',$val['id'])->update($val);
                    }else{
                        $val['user_id']=Auth::id();
                        $event->steps()->create($val);
                    }
                }
            }
            if($request['is_open']==true){
                $count = Event::where('is_open', true)->where('id','!=',$id)->count();
                if($count>1){
                    $request['is_open']=false;
                }
            }
            $event->update($request->all());
            DB::commit();
            return response()->json([
                "status" => "200",
                "message" => 'Modificación exitosa',
                "type" => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "status" => "500",
                "message" => $e->getMessage(),
                "type" => 'error'
            ]);
        }
    }
   
    /**
     * Función para eliminar un event
     * @param  int $id
     * @return json
     */
    public function delete($id)
    {
        $data = Event::find($id);
        $data->delete();
      
        return response()->json([
            "status" => "200",
            "message" => 'Eliminación exitosa',
            "type" => 'success'
        ]);
    }
    /**
     * Función para obtener todos los event
     * @return json
     */
    public function index()
    {
        $data = Event::with('steps')->orderBy('id', 'desc')->get();
        return response()->json([
            "status" => "200",
            "data" => $data,
            "message" => 'Listado exitoso',
            "type" => 'success'
        ]);
    }
    /**
     * Función para obtener los datos del evento actual
     * @param int $id 
     * @return json
     */
    public function showCurrentEvent()
    {
        $data = Event::with('steps')->where('is_open', true)->first();
        return response()->json([
            "status" => "200",
            "data" => $data,
            "message" => 'Listado exitoso',
            "type" => 'success'
        ]);
    }
      /**
     * Función para abrir una competencia
     * @param int $id 
     * @return json
     */
    public function updateEvent($id)
    {
        Event::where('is_open', true)->update(['is_open' => false]);
        $data = Event::find($id)->update(['is_open' => true]);
        return response()->json([
            "status" => "200",
            "data" => $data,
            "message" => 'Listado exitoso',
            "type" => 'success'
        ]);
    }
}
