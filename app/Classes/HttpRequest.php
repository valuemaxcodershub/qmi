<?php
namespace App\Classes;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class HttpRequest  {

    /**
     * send post request
     * @param url 
     * @param header
     * @param options
     * @param body
     * @return json object
     * 
     */

    public static function sendPost(string $endpoint, $body, array $header = []) {
        try {
            $sendPost = Http::withHeaders($header)->post($endpoint, $body);
            return $sendPost;
        } catch (RequestException $e) {
            if ($e->getCode() == CURLE_OPERATION_TIMEOUTED) {
                // Handle the timeout error
                // For example, you can log the error or display a custom error message
                Log::error('cURL error 28: Operation timed out');
                // Or display a custom error message to the user
                return response()->json(['error' => 'Request timed out'], 500);
            } else {
                // Handle other cURL errors
                // For example, you can log the error or display a custom error message
                Log::error($e->getMessage());
                // Or display a generic error message to the user
                return response()->json(['error' => 'An error occurred'], 500);
            }
        } catch (\Exception $exception) {
            // handle other exceptions
            return response()->json(['error' => $exception->getMessage()]);
        }
    }

    public static function sendGet(string $endpoint, string $body = "", array $header = []) {
        try {
            if(count($header) > 0) {
                $sendGet = Http::withHeaders($header)->get($endpoint, $body);
            } else {
                $sendGet = Http::get($endpoint, $body);
            }
            return $sendGet;
        } catch (RequestException $e) {
            if ($e->getCode() == CURLE_OPERATION_TIMEOUTED) {
                // Handle the timeout error
                // For example, you can log the error or display a custom error message
                Log::error('cURL error 28: Operation timed out');
                // Or display a custom error message to the user
                return response()->json(['error' => 'Request timed out'], 500);
            } else {
                // Handle other cURL errors
                // For example, you can log the error or display a custom error message
                Log::error($e->getMessage());
                // Or display a generic error message to the user
                return response()->json(['error' => 'An error occurred'], 500);
            }
        }
    }

    public static function sendDelete(string $endpoint, string $body = "", array $header = []) {
        try {
            
            if(count($header) > 0) {
                $sendGet = Http::withHeaders($header)->delete($endpoint, $body);
            } else {
                $sendGet = Http::delete($endpoint, $body);
            }
            return $sendGet;
        } catch (RequestException $exception) {
            // handle the request exception
            return $exception->response->json();
        } catch (\Exception $exception) {
            // handle other exceptions
            return $exception->getMessage();
        }
    }
    

}
?>