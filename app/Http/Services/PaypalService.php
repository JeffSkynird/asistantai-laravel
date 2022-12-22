<?php
namespace App\Http\Services;

use CURLFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaypalService
{
    private $paypalServer;
    private $token;
    private $clientId;
    private $clientSecret;
    public function __construct()
    {
        $this->paypalServer = env('PAYPAL_SERVER');
        $this->clientId = env('PAYPAL_CLIENT_ID');
        $this->clientSecret = env('PAYPAL_CLIENT_SECRET');
        $this->token = "A21AAIGrvLci4gSdb96Ly1r1lWKxkxDzvtvIKnFg82Y5cEqnHQjAlcrBYi5srTC7o3CLPE5IYP-X5TleR6jUKdu-a2jwtAutQ";//$this->crearAccessToken();
    }
    public function cancelSubscription($id)
    {
        $url = $this->paypalServer.'/v1/billing/subscriptions/'.$id.'/cancel';
        Log::info($url);
        $arr = array('reason' =>'Cancelación manual');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($arr),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer $this->token"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        Log::info($response);
        return json_decode($response,true);
    }
    public function activateSubscription($id)
    {
        Log::info("activacion manual");
        Log::info($id);
        $url = $this->paypalServer.'/v1/billing/subscriptions/'.$id.'/activate';
        $arr = array('reason' =>'Activacion manual');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($arr),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer $this->token"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        Log::info($response);
        return json_decode($response,true);
    }
    public function paymentSubscription($id)
    {
        $user = Auth::user();
        $url = $this->paypalServer.'/v1/billing/subscriptions';
        $arr =  array(
            "plan_id" => $id,
            "subscriber" => array(
                "name" => array(
                    "given_name" => $user->names,
                    "surname" => $user->last_names
                ),
                "email_address" => $user->email
            ),
            "application_context" => array(
                "return_url" => "https://67fd-157-100-110-34.ngrok.io/ok",
                "cancel_url" => "https://67fd-157-100-110-34.ngrok.io/error"
            )
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($arr),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer $this->token"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response,true);
    }
    public function crearAccessToken(){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->paypalServer.'/v1/oauth2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "grant_type=client_credentials",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded",
                "Authorization : Basic ".base64_encode($this->clientId.":".$this->clientSecret),
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response,true)['access_token'];
    }

}