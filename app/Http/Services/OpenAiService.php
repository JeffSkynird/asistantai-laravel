<?php
namespace App\Http\Services;

use CURLFile;
use Illuminate\Support\Facades\Log;

class OpenAiService
{
    private $openApiServer;
    private $token;
    public function __construct($token)
    {
        $this->openApiServer = env('OPEN_API_SERVER');
        $this->token = $token;
    }
    public function generate($data,$command)
    {
        $arr = array('prompt' => $data, 'command' => $command,'token' => $this->token);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->openApiServer.'generate',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($arr),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response,true)['result'];
    }

    

    public function generateWhisper($file)
    {
        $arr = array('file' => new CURLFile ( $file ));
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->openApiServer.'whisper',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $arr,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: multipart/form-data"
            ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response,true)['result'];
    }
}