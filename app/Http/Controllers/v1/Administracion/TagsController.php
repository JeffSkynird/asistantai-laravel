<?php

namespace App\Http\Controllers\v1\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Generation;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use \Validator;

class TagsController extends Controller
{
    /**
     * Función para crear nuevos categories
     * @param Request $request 
     * @return json
     */
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();

            $vacios = Validator::make($request->all(), [
                'name' => 'required',
                'generation_id'=>'required'
            ]);
            if ($vacios->fails()) {
                return response([
                    'message' => "No debe dejar campos vacíos",
                    'fields' => $request->all(),
                    'type' => "error",
                ]);
            }
            //existe un registro con el mismo nombre
            $existe = Tag::where('name', $params['name'])->where('user_id', Auth::id())->first();
            if ($existe) {
                return response([
                    'message' => "Tag ya creado",
                    'fields' => $request->all(),
                    'type' => "error",
                ]);
            }
            $params['user_id'] = Auth::id();
          $tag = Tag::create($params);
            $generation = Generation::find($params['generation_id']);
            $generation->tag_id = $tag->id;
            $generation->save();

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
     * Función para obtener los datos de un categories
     * @param int $id 
     * @return json
     */
    public function show($id)
    {
        $data = Tag::find($id);
        return response()->json([
            "status" => "200",
            "message" => 'Datos obtenidos con éxito',
            "data" => $data,
            "type" => 'success'
        ]);
    }
    /**
     * Función para modificar los datos de un categories
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
        DB::beginTransaction();
        try {
            $category = Tag::find($id);
            $category->update($request->all());
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
     * Función para eliminar un categories
     * @param  int $id
     * @return json
     */
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $generations = Generation::where('tag_id', $id)->count();
            if($generations > 0){
                return response()->json([
                    "status" => "500",
                    "message" => 'Error: Hay generaciones usando el tag',
                    "type" => 'error'
                ]);
            }
            $data = Tag::find($id);
            $data->delete();
            DB::commit();
            return response()->json([
                "status" => "200",
                "message" => 'Eliminación exitosa',
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
     * Función para obtener todos los categories
     * @return json
     */
    public function index()
    {
        $data = Tag::where('user_id', Auth::id())->orderBy('id', 'desc')->get(); 
        return response()->json([
            "status" => "200",
            "data" => $data,
            "message" => 'Listado exitoso',
            "type" => 'success'
        ]);
    }

    
    public function deleteByGeneration($id){
        $data = Generation::find($id);
        if(!$data){
            return response()->json([
                "status" => "500",
                "message" => 'No se encontró el registro',
                "type" => 'error'
            ]);
        }
        $data->tag_id = null;
        $data->save();
        return response()->json([
            "status" => "200",
            "message" => 'Modificación exitosa',
            "type" => 'success'
        ]);
    }

    public function asignarTag(Request $request){
        $data = Generation::find($request->generation_id);
        if(!$data){
            return response()->json([
                "status" => "500",
                "message" => 'No se encontró el registro',
                "type" => 'error'
            ]);
        }
        $data->tag_id = $request->tag_id;
        $data->save();
        return response()->json([
            "status" => "200",
            "message" => 'Modificación exitosa',
            "type" => 'success'
        ]);
    }
}
