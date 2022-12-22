<?php

namespace App\Http\Controllers\v1\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use \Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('id', 'desc')->get();
        return json_encode([
            "status" => "200",
            "data" => $usuarios,
            "message" => 'Listado exitoso',
            "type" => 'success'
        ]);
    }
    public function create(Request $request)
    {
        try {
            $params = $request->all();
            $vacios = Validator::make($request->all(), [
                'names' => 'required',
                'last_names' => 'required',
                'email' => 'required',
                'password' => 'required'
            ]);
            if ($vacios->fails()) {
                return response([
                    'message' => "No debe dejar campos vacíos",
                    'fields' => $request->all(),
                    'type' => "error",
                ]);
            }
            User::create($params);
            return json_encode([
                "status" => "200",
                "message" => 'Registro exitoso',
                "type" => 'success'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                "status" => "500",
                "message" => $e->getMessage(),
                "type" => 'error'
            ]);
        }
    }
    public function show($id)
    {
        $data = User::with(['subscription' => function ($query) {
            $query->where('status', 'activa');
        }])->find($id);
        return json_encode([
            "status" => "200",
            "message" => 'Datos obtenidos con éxito',
            "data" => $data,
            "type" => 'success'
        ]);
    }
    public function showAuth()
    {
        $auth = Auth::id();
        $data = User::with(['subscriptions' => function ($query) {
            $query->where('status', 'activa');
        }])->find($auth);
        $plan = null;
        if ($data->subscriptions->first()) {
            $idTemp = $data->subscriptions->first()->membership_id;
            $plan = Membership::select('name', 'price')->where('id', $idTemp)->first();
        }
        return json_encode([
            "status" => "200",
            "message" => 'Datos obtenidos con éxito',
            "data" => $data,
            "plan" => $plan,
            "type" => 'success'
        ]);
    }
    public function update(Request $request, $id)
    {
        $names = $request->input('names');
        $lastNames = $request->input('last_names');
        $email = $request->input('email');
        $password = $request->input('password');

        try {
            $vacios = Validator::make($request->all(), [
                'names' => 'required',
                'last_names' => 'required',
                'email' => 'required'
            ]);
            if ($vacios->fails()) {
                return response([
                    'message' => "No debe dejar campos vacíos",
                    'fields' => $request->all(),
                    'type' => "error",
                ]);
            }

            $user = User::find($id);
            $user->names = $names;
            $user->last_names = $lastNames;
            $user->email = $email;
            if (!is_null($password)) {
                $user->password = $password;
            }
            $user->save();
            return json_encode([
                "status" => "200",
                "message" => 'Modificación exitosa',
                "type" => 'success'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                "status" => "500",
                "message" => $e->getMessage(),
                "type" => 'error'
            ]);
        }
    }
    public function updateAuth(Request $request)
    {
        $userAuth = Auth::user();
        $names = $request->input('names');
        $lastNames = $request->input('last_names');
        $email = $request->input('email');
        $password = $request->input('password');
        $openAiToken  = $request->input('open_ai_token');
        $vacios = Validator::make($request->all(), [
            'names' => 'required',
            'last_names' => 'required',
            'email' => 'required'
        ]);
        if ($vacios->fails()) {
            return response([
                'message' => "Revise los campos ingresados",
                'type' => "error",
            ]);
        }
        try {
            $user = User::find($userAuth->id);
            $user->names = $names;
            $user->last_names = $lastNames;
            $user->email = $email;
            if (!is_null($password)) {
                $user->password = $password;
            }
            if (!is_null($openAiToken)) {
                $user->open_ai_token =  Crypt::encryptString($openAiToken);
            }
            $user->save();

            return json_encode([
                "status" => "200",
                "message" => 'Modificación exitosa',
                "type" => 'success'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                "status" => "500",
                "message" => $e->getMessage(),
                "type" => 'error'
            ]);
        }
    }
    public function obtenerToken($id){
        try {
            //$user = Auth::user();
            $user = User::find($id);
            $decrypted = Crypt::decryptString($user->open_ai_token);
            return json_encode([
                "status" => "200",
                "message" => 'Datos obtenidos con éxito',
                "type" => 'success',
                "data" => $decrypted
            ]);
        } catch (DecryptException $e) {
            return json_encode([
                "status" => "500",
                "message" => $e->getMessage(),
                "type" => 'error'
            ]);
        }
    }
    public function delete($id)
    {
        $data = User::find($id);
        $data->delete();
        return json_encode([
            "status" => "200",
            "message" => 'Eliminación exitosa',
            "type" => 'success'
        ]);
    }
    public function subscribe($user, $membershipId)
    {
        $mem = Membership::find($membershipId);
        if (is_null($mem)) {
            throw new \Exception("La membresía no existe");
        }
        $user = User::find($user);
        if (is_null($user)) {
            throw new \Exception("El usuario no existe");
        }
        Subscription::create([
            "user_id" => $user->id,
            "membership_id" => $mem->id,
            'start_date' => date('Y-m-d'),
            'next_payment_date' => date('Y-m-d', strtotime('+1 month')),
            'status' => 'activa'
        ]);
        return json_encode([
            "status" => "200",
            "message" => 'Suscripción exitosa',
            "type" => 'success'
        ]);
    }
    public function cancelSubscription()
    {
        try {
            $user = Auth::user();
            $subscription = Subscription::where('user_id', $user->id)->first();
            if (is_null($subscription)) {
                throw new \Exception("No tiene una suscripción activa");
            }
            $subscription->status = 'inactiva';
            $subscription->save();
            return json_encode([
                "status" => "200",
                "message" => 'Suscripción cancelada',
                "type" => 'success'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                "status" => "500",
                "message" => $e->getMessage(),
                "type" => 'error'
            ]);
        }
    }
    public function changeMembership(Request $request){
        $user = Auth::user();
        $membershipId = $request->input('membership_id');
        $mem = Membership::find($membershipId);
        if (is_null($mem)) {
            throw new \Exception("La membresía no existe");
        }
        $user = User::find($user->id);  
        $subscription = Subscription::where('user_id', $user->id)->first();
        if (is_null($subscription)) {
            throw new \Exception("No tiene una suscripción activa");
        }
        $subscription->membership_id = $mem->id;
        $subscription->save();
    }
}
