<?php

namespace App\Http\Controllers\v1\Administracion;

use App\Http\Controllers\Controller;
use App\Http\Services\PaypalService;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \Validator;

class MembershipController extends Controller
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
                'price' => 'required',
                'frecuency' => 'required',
            ]);
            if ($vacios->fails()) {
                return response([
                    'message' => "No debe dejar campos vacíos",
                    'fields' => $request->all(),
                    'type' => "error",
                ]);
            }
            Membership::create($params);
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
        $data = Membership::where('id', $id)->first();
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
            'name' => 'required',
            'price' => 'required',
            'frecuency' => 'required',
        ]);
        if ($vacios->fails()) {
            return response([
                'message' => "No deje campos vacíos",
                'type' => "error",
            ]);
        }
        DB::beginTransaction();
        try {
            $category = Membership::find($id);
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
            $data = Membership::find($id);
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
        $data = Membership::orderBy('id', 'asc')->get();
        return response()->json([
            "status" => "200",
            "data" => $data,
            "message" => 'Listado exitoso',
            "type" => 'success'
        ]);
    }
    public function updateMembership(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $mem = Membership::where('plan_paypal_id', $request->plan_id)->first();
        try {
            DB::beginTransaction();
            if (!$mem) {
                return response()->json([
                    "status" => "200",
                    "message" => 'No se encontro membresia',
                    "type" => 'success'
                ]);
            }
            if (!$user) {
                if ($request->status == 'ACTIVE') {
                    $userTemp = User::create([
                        'names' => $request->name,
                        'last_names' => $request->last_name,
                        'email' => $request->email,
                        'password' => bcrypt($request->email)
                    ]);
                    $userTemp->subscriptions()->create([
                        "membership_id" => $mem->id,
                        'start_date' => $request->start_subscription,
                        'next_payment_date' => $request->end_subscription,
                        'status' => 'activa',
                        'subscription_paypal_id' => $request->subscription_id
                    ]);
                } else {
                    return response()->json([
                        "status" => "200",
                        "message" => 'No se encontro usuario',
                        "type" => 'success'
                    ]);
                }
            } else {
                if ($user->subscriptions()->where('status', 'activa')->count() > 0) {
                    if ($request->status == 'EXPIRED' || $request->status == 'CANCELLED') {
                        $user->subscriptions()->where('status', 'activa')->update([
                            'status' => 'cancelada'
                        ]);
                    }else if($request->status == 'ACTIVE'){
                        $user->subscriptions()->delete();
                        $user->subscriptions()->create([
                            "user_id" => $user->id,
                            "membership_id" => $mem->id,
                            'start_date' => $request->start_subscription,
                            'next_payment_date' => $request->end_subscription,
                            'status' => 'activa',
                            'subscription_paypal_id' => $request->subscription_id
                        ]);
                    }
                } else {
                    if ($request->status == 'ACTIVE') {
                        if ($mem) {
                            $user->subscriptions()->delete();
                            $user->subscriptions()->create([
                                "user_id" => $user->id,
                                "membership_id" => $mem->id,
                                'start_date' => $request->start_subscription,
                                'next_payment_date' => $request->end_subscription,
                                'status' => 'activa',
                                'subscription_paypal_id' => $request->subscription_id
                            ]);
                        }
                    }
                }
            }
            DB::commit();
            return response()->json([
                "status" => "200",
                "message" => 'Activación exitosa',
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

    public function cancelMembership()
    {
        try {
            DB::beginTransaction();

            $userAuth = Auth::user()->id;
            $user = User::find($userAuth);
            if (!$user) {
                return response()->json([
                    "status" => "200",
                    "message" => 'No se encontró el usuario',
                    "type" => 'success'
                ]);
            }
            $ps = new PaypalService();
            $id = $user->subscriptions()->where('status', 'activa')->first();
            if(!$id){
                return response()->json([
                    "status" => "200",
                    "message" => 'No tiene subscripciones activas',
                    "type" => 'success'
                ]);
            }
            $ps->cancelSubscription($id->subscription_paypal_id);

            $user->subscriptions()->where('status', 'activa')->update([
                'status' => 'cancelada'
            ]);
            DB::commit();

            return response()->json([
                "status" => "200",
                "message" => 'Cancelación exitosa',
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

    public function payment(Request $request){
        try{
            $id = $request->plan_id;
            DB::beginTransaction();
            $ps = new PaypalService();
            $data = $ps->paymentSubscription($id);
            $linkAprove ="";
            foreach ($data['links'] as $link){
                if($link['rel'] == "approve"){
                    $linkAprove = $link['href'];
                }
            }
            DB::commit();
            return response()->json([
                "status" => "200",
                "data" => $linkAprove,
                "message" => 'Activación en proceso',
                "type" => 'success'
            ]);
        }catch (\Exception $e){
            DB::rollBack();

            return response()->json([
                "status" => "500",
                "message" => $e->getMessage(),
                "type" => 'error'
            ]);
        }
    }
    public function activateMembership()
    {
        try {
            DB::beginTransaction();

            $userAuth = Auth::user()->id;
            $user = User::find($userAuth);
            if (!$user) {
                return response()->json([
                    "status" => "200",
                    "message" => 'No se encontró el usuario',
                    "type" => 'success'
                ]);
            }
            $ps = new PaypalService();
            $id = $user->subscriptions()->where('status', 'cancelada')->first();
            if (!$id) {
                return response()->json([
                    "status" => "200",
                    "message" => 'No tiene subscripciones canceladas',
                    "type" => 'success'
                ]);
            }
            $ps->activateSubscription($id->subscription_paypal_id);
            $user->subscriptions()->where('status', 'cancelada')->update([
                'status' => 'activa'
            ]);
            DB::commit();
            return response()->json([
                "status" => "200",
                "message" => 'Cancelación exitosa',
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
}
