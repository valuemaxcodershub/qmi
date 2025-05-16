<?php 

namespace App\Services;

use App\Models\User;
use App\Traits\Processor;
use App\Classes\HttpRequest;
use App\Model\PaymentRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Mpdf\Http\Exception\RequestException;

class MonnifyService {
    use Processor;

    private PaymentRequest $payment;
    private $user, $endpoint, $headerParams;
    protected $responseBody;

    private $v1 = "api/v1/";
    private $v2 = "api/v2/";

    public function __construct(PaymentRequest $payment, User $user)
    {
        $config = $this->payment_config('monnify', 'payment_config');
        $values = false;
        if (!is_null($config) && $config->mode == 'live') {
            $this->endpoint = 'https://api.monnify.com/';
            $values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->endpoint = 'https://sandbox.monnify.com/';
            $values = json_decode($config->test_values);
        }

        if ($values) {
            $config = array(
                'publicKey' => env('MONNIFY_PUBLIC_KEY', $values->public_key),
                'secretKey' => env('MONNIFY_SECRET_KEY', $values->secret_key),
            );
            Config::set('monnify', $config);
        }

        $this->payment = $payment;
        $this->user = $user;

        $this->headerParams = [
            "Content-Type" => "application/json",
            "Cache-Control" => "no-cache",
        ];
    }

    public function generateAuthToken() {
        try {
            $publicKey = Config::get('monnify.publicKey');
            $secretKey = Config::get('monnify.secretKey');

            $result = HttpRequest::sendPost($this->endpoint.$this->v1."auth/login", [], [
                "Authorization" => "Basic ".base64_encode($publicKey.':'.$secretKey),
                "Content-Type" => "application/json"
            ]);

            $accessToken = json_decode((string) $result->body());
            $bearerToken = isset($accessToken->responseBody->accessToken) ? $accessToken->responseBody->accessToken : false;
            $this->responseBody = $bearerToken;
        } catch (RequestException $e) {
            // Handle request exceptions (e.g. 4xx, 5xx status codes)
            $this->responseBody = [
                "status_code" => $e->getCode(),
                "message" => $e->getMessage()
            ];
        } catch (\Exception $e) {
            // Handle other exceptions (e.g. network errors)
            $this->responseBody = ["message" => $e->getMessage()];
        }
        return $this->responseBody;
    }

    public function verifyNIN($ninNumber) {
        try {

            $body = json_encode([
                "nin" => $ninNumber
            ]);
            
            $accessToken = $this->generateAuthToken();

            $result = HttpRequest::sendPost($this->endpoint.$this->v1.'vas/nin-details', [
                "nin" => $ninNumber
            ], [
                'Authorization' => "Bearer ".$accessToken,
                'Content-Type' => 'application/json'
            ]);
            $this->responseBody = $result->body();
        } catch (RequestException $e) {
            // Handle request exceptions (e.g. 4xx, 5xx status codes)
            $this->responseBody = [
                "status_code" => $e->getCode(),
                "message" => $e->getMessage()
            ];
        } catch (\Exception $e) {
            // Handle other exceptions (e.g. network errors)
            $this->responseBody = ["message" => $e->getMessage()];
        }
        return $this->responseBody;        
    }

}