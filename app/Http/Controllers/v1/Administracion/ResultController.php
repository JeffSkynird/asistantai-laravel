<?php

namespace App\Http\Controllers\v1\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Exam;
use App\Models\Order;
use App\Models\Planning;
use App\Models\Result;
use App\Models\ValueType;
use Carbon\Carbon;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use \Validator;

class ResultController extends Controller
{

    public function saveImgur($file)
    {
        $curl = curl_init("https://api.imgur.com/3/image");
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POSTFIELDS => array('image' => $file, 'type' => 'base64', 'name' => 'image.jpg'),
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                'Authorization: Client-ID 07c723a1c44ff65',
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }
    public function getRemoteImage($name)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.imgur.com/3/image/' . $name,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Client-ID 07c723a1c44ff65',
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }
    public function deleteRemoteImage($name)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.imgur.com/3/image/' . $name,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Client-ID 07c723a1c44ff65',
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }

    public function validateFile($data)
    {
        if (base64_encode(base64_decode($data, true)) === $data) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Función para crear nuevos people
     * @param Request $request 
     * @return json
     */
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $vacios = Validator::make($request->all(), [
                'order_id' => 'required',
                'results' => 'required'
            ]);
            if ($vacios->fails()) {
                return response([
                    'message' => "No debe dejar campos vacíos",
                    'fields' => $request->all(),
                    'type' => "error",
                ]);
            }
            $params['user_id'] = Auth::id();
            foreach ($params['results'] as $result) {
                $exm = Exam::find($result['exam_id']);
                if ($this->validaTipo($result['value'], $exm->value_type_id) == false) {
                    $vl = ValueType::find($exm->value_type_id);
                    throw new \ErrorException('El valor del examen ' . $exm->name . ' debe ser de tipo ' . $vl->name);
                }
                $planning = Planning::where('order_id', $params['order_id'])->where('exam_id', $result['exam_id'])->first();
                //pregunto si es tipo archivo
                if ($exm->value_type_id == 4) {
                    if ($this->validateFile($result['value']) == true) {
                        $obj = $this->saveImgur($result['value']);
                        $planning->value = $obj['data']['link'];
                    }
                    //$request['delete_hash']=$obj['data']['deletehash'];
                } else {
                    $planning->value = $result['value'];
                }
                $planning->is_complete = true;
                $planning->save();
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
    function toBool($var)
    {
        switch (strtolower($var)) {
            case '1':
            case 'true':
            case 'on':
            case 'yes':
            case 'y':
            case 'si':
            case 'pos':
            case 'neg':
            case 'positivo':
            case 'negativo':
            case 'no':
                return true;
            default:
                return false;
        }
    }
    public function validaTipo($value, $id)
    {
        if ($id == 1) {
            if (!is_numeric($value)) {
                return false;
            }
        } else if ($id == 3) {
            if (!$this->toBool($value)) {
                return false;
            }
        }
        return true;
    }
    /**
     * Función para obtener los datos de un people
     * @param int $id 
     * @return json
     */
    public function show($id)
    {
        $data = Planning::where('order_id', $id)->with('exam')->get();
        return response()->json([
            "status" => "200",
            "message" => 'Datos obtenidos con éxito',
            "data" => $data,
            "type" => 'success'
        ]);
    }

    public function showResults($id)
    {
        $data = Planning::where('order_id', $id)->with('exam.valueType', 'exam.category')->get();
        return response()->json([
            "status" => "200",
            "message" => 'Datos obtenidos con éxito',
            "data" => $data,
            "type" => 'success'
        ]);
    }

    public function showResultsByPacient($id)
    {
        $order = Order::where('pacient_id', $id)->first();
        $data = Planning::where('order_id', $order->id)->with('exam.valueType', 'exam.category')->get();
        return response()->json([
            "status" => "200",
            "message" => 'Datos obtenidos con éxito',
            "data" => $data,
            "type" => 'success'
        ]);
    }

    public function printPdf($id)
    {
        $order = Order::where('id', $id)->first();
        $pacient = $order->pacient;
        $data = DB::table('plannings')
            ->join('exams', 'plannings.exam_id', '=', 'exams.id')
            ->join('categories', 'exams.category_id', '=', 'categories.id')
            ->where('plannings.order_id', $order->id)
            ->select('categories.name as category', 'exams.name as exam', 'plannings.value', 'exams.description','exams.unity')
            ->get()->groupBy('category');
        $bornDate = date('Y') - Carbon::parse($pacient->born_date)->format('Y');
        $pdf =  PDF::loadView('results', ['pacient' => $pacient, 'data' => $data, 'order_date' => $order->created_at,'borndate'=>$bornDate]);
        return  $pdf->stream('whateveryourviewname.pdf');
    }
}
