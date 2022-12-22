<?php

namespace App\Http\Controllers\v1\Administracion;

use App\Http\Controllers\Controller;
use App\Http\Services\GenerationService;
use App\Jobs\Generator;
use App\Models\Generation;
use App\Models\Result;
use App\Models\Subscription;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use \Validator;

class GenerationController extends Controller
{
    public function jobInit()
    {
        Generator::dispatch();
        return response()->json([
            "status" => "200",
            "message" => 'Job iniciado',
            "type" => 'success'
        ]);
    }
    public function obtenerToken($id)
    {
        $token = "";
        try {
            $user = User::find($id);
            $decrypted = Crypt::decryptString($user->open_ai_token);
            $token =  $decrypted;
        } catch (DecryptException $e) {
            return json_encode([
                "status" => "500",
                "message" => $e->getMessage(),
                "type" => 'error'
            ]);
        }
        return $token;
    }
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
                'generation_type_id' => 'required',
                'prompt' => 'required',
                'command' => 'required'
            ]);
            if ($vacios->fails()) {
                return response([
                    'message' => "No debe dejar campos vacíos",
                    'fields' => $request->all(),
                    'type' => "error",
                ]);
            }
            $auth = Auth::user();
            $subs = Subscription::where('user_id', $auth->id)->where('status', 'activa')->first();
            if (!$subs) {
                return response()->json([
                    "status" => "500",
                    "message" => 'No tiene una suscripción activa',
                    "type" => 'error'
                ]);
            }
            //compruebo si la subscripcion tiene permitido el tipo de generacion

            if ($subs->membership_id == 1) {
                if ($params['generation_type_id'] != 1 && $params['generation_type_id'] != 2 && $params['generation_type_id'] != 3) {
                    return response()->json([
                        "status" => "500",
                        "message" => 'No tiene permitido generar contenido de este tipo',
                        "type" => 'error'
                    ]);
                }
            } else if ($subs->membership_id == 2) {
                if ($params['generation_type_id'] != 1 && $params['generation_type_id'] != 2 && $params['generation_type_id'] != 3 && $params['generation_type_id'] != 4 && $params['generation_type_id'] != 5 && $params['generation_type_id'] != 6) {
                    return response()->json([
                        "status" => "500",
                        "message" => 'No tiene permitido generar contenido de este tipo',
                        "type" => 'error'
                    ]);
                }
            }
            if ($params['generation_type_id'] != 1) {
                if (!$request->hasFile('file')) {
                    return response()->json([
                        "status" => "500",
                        "message" => 'Debe subir un archivo',
                        "type" => 'error'
                    ]);
                }
            }
            $params['user_id'] = 1;
            $params['status'] = 'pendiente';
            $params['result'] = 'Procesando';
            $params['prompt'] = $params['generation_type_id'] == 1 ? $params['prompt'] : 'Archivo subido';
            $gen = Generation::create($params);

            $this->queueGeneration($params['generation_type_id'], $params['prompt'], $params['command'], $request->file('file'), $gen->id);
            /*   if($params['generation_type_id']==1){ //TEXTO
               // $params['result'] = $generateService->generate($params['prompt'], $params['command']);
               Generator::dispatch($params['prompt'], $params['command'], $params['generation_type_id'], $gen->id,$this->obtenerToken($auth->id));
            }else if($params['generation_type_id']==2){ //PDF
                $file = $request->file('file');
                $path = $this->saveFile($file);
                //$params['result'] = $generateService->extractPdfAndGenerate($pdf, $params['command']);
                Generator::dispatch($path, $params['command'], $params['generation_type_id'], $gen->id,$this->obtenerToken($auth->id));
            }else if($params['generation_type_id']==3){ //IMAGEN
                $file = $request->file('file');
                $path = $this->saveFile($file);
                //$params['result'] = $generateService->extractImageAndGenerate($image, $params['command']);
                Generator::dispatch($path, $params['command'], $params['generation_type_id'], $gen->id,$this->obtenerToken($auth->id));
            }else if($params['generation_type_id']==4){ //AUDIO
                $file = $request->file('file');
                $path = $this->saveFile($file);
                //$params['result'] = $generateService->extractFromAudio($file, $params['command']);
                Generator::dispatch($path, $params['command'], $params['generation_type_id'], $gen->id,$this->obtenerToken($auth->id));
            }else if($params['generation_type_id']==5){ //VIDEO
                $file = $request->file('file');
                $path = $this->saveFile($file);
                //$audio = $generateService->extractAudioFromVideo($file); 
                //$params['result'] = $generateService->extractFromAudio($audio, $params['command']);
                Generator::dispatch($path, $params['command'], $params['generation_type_id'], $gen->id,$this->obtenerToken($auth->id));
            } */
            DB::commit();
            return response()->json([
                "status" => "200",
                "message" => 'Generación exitosa',
                'generation_id' => $gen->id,
                'result' => $params['result'],
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
    public function queueGeneration($generationType, $prompt, $command, $file, $genId)
    {
        $auth = Auth::user();
        if ($generationType == 1) { //TEXTO
            Generator::dispatch($prompt, $command, $generationType, $genId, $this->obtenerToken($auth->id));
        } else if ($generationType == 2) { //PDF
            $path = $this->saveFile($file);
            Generator::dispatch($path, $command, $generationType, $genId, $this->obtenerToken($auth->id));
        } else if ($generationType == 3) { //IMAGEN
            $path = $this->saveFile($file);
            Generator::dispatch($path, $command, $generationType, $genId, $this->obtenerToken($auth->id));
        } else if ($generationType == 4) { //AUDIO
            $path = $this->saveFile($file);
            Generator::dispatch($path, $command, $generationType, $genId, $this->obtenerToken($auth->id));
        } else if ($generationType == 5) { //VIDEO
            $path = $this->saveFile($file);
            Generator::dispatch($path, $command, $generationType, $genId, $this->obtenerToken($auth->id));
        } else if ($generationType == 0) {
            Generator::dispatch($prompt, $command, $generationType, $genId, $this->obtenerToken($auth->id));
        }
    }

    public function exportPdf($id)
    {
        $gen = Generation::find($id);
        if (!$gen) {
            return response()->json([
                "status" => "500",
                "message" => 'No se encontró el registro',
                "type" => 'error'
            ]);
        }
        $results = $gen->results;
        $pdf = PDF::loadView('generation', ['data' => $results]);
        return $pdf->download('result.pdf');
    }
    public function updateResults($id, Request $request)
    {
        $params = $request->all();
        $params['result'] = "Procesando";
        $gen = Generation::find($id);
        $gen->update([
            'result' => 'Procesando',
            'status' => 'pendiente'
        ]);
        if (!$gen) {
            return response()->json([
                "status" => "500",
                "message" => 'No se encontró el registro',
                "type" => 'error'
            ]);
        }
        $this->queueGeneration(0, null, $params['command'], null, $id);
        return response()->json([
            "status" => "200",
            "message" => 'Generación exitosa',
            'result' => $params['result'],
            "type" => 'success'
        ]);
    }
    //funcion que guarda el archivo en el storage y retorna la ruta como \Symfony\Component\HttpFoundation\File\File
    public function saveFile($file)
    {
        $path = Storage::putFile('', $file);
        $path = Storage::path($path);
        return $path;
    }

    /**
     * Función para obtener los datos de un categories
     * @param int $id 
     * @return json
     */
    public function show($id)
    {
        $data = Generation::where('id', $id)->first();
        if (!$data) {
            return response()->json([
                "status" => "500",
                "message" => 'No se encontró el registro',
                "type" => 'error'
            ]);
        }
        return response()->json([
            "status" => "200",
            "message" => 'Datos obtenidos con éxito',
            "data" => $data,
            "type" => 'success'
        ]);
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
            $data = Generation::find($id);
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
    public function index(Request $request)
    {
        $tagId = $request->input('tag_id');
        if ($tagId != null && $tagId != '' && $tagId != 'undefined' && $tagId != 0) {
            $data = Generation::with('generationType', 'tag')->where('tag_id', $tagId)->orderBy('id', 'desc')->get();
            return response()->json([
                "status" => "200",
                "data" => $data,
                "message" => 'Listado exitoso',
                "type" => 'success'
            ]);
        } else {
            $data = Generation::with('generationType', 'tag')->orderBy('id', 'desc')->get();
        }
        return response()->json([
            "status" => "200",
            "data" => $data,
            "message" => 'Listado exitoso',
            "type" => 'success'
        ]);
    }

    public function getLastGenerations()
    {
        //Obtiene las ultimas 5 generaciones del usuario logueado
        $auth = Auth::user();
        $data = Generation::with('generationType')->where('user_id', $auth->id)->orderBy('id', 'desc')->take(5)->get();
        return response()->json([
            "status" => "200",
            "data" => $data,
            "message" => 'Listado exitoso',
            "type" => 'success'
        ]);
    }
    public function getGenerationKpis()
    {
        $auth = Auth::user();
        $data = Generation::where('user_id', $auth->id)->get();
        $pendientes = $data->where('status', 'pendiente')->count();
        $finalizadas = $data->where('status', 'finalizado')->count();
        return response()->json([
            "status" => "200",
            "data" => [
                'pendientes' => $pendientes,
                'finalizadas' => $finalizadas
            ],
            "message" => 'Listado exitoso',
            "type" => 'success'
        ]);
    }

    public function getByTag($id)
    {
        $data = Generation::with('tag')->whereHas('tags', function ($q) use ($id) {
            $q->where('tag_id', $id);
        })->get();
        return response()->json([
            "status" => "200",
            "data" => $data,
            "message" => 'Listado exitoso',
            "type" => 'success'
        ]);
    }

    public function getResults($id)
    {
        $data = Result::where('generation_id', $id)->get();
        return response()->json([
            "status" => "200",
            "data" => $data,
            "message" => 'Listado exitoso',
            "type" => 'success'
        ]);
    }
}
