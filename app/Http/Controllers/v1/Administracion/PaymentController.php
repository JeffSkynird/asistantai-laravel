<?php

namespace App\Http\Controllers\v1\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function pay()
    {
        DB::beginTransaction();
        try {
            $auth = Auth::user();
            $subs = Subscription::where('user_id', $auth->id)->update([
                'next_payment_date' => date('Y-m-d', strtotime('+1 month'))
            ]);
            Payment::create([
                'subscription_id' => $subs->id,
                'status' => 'Pagado'
            ]);
            DB::commit();
            return response()->json([
                "status" => "200",
                "message" => 'Pago exitoso',
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
