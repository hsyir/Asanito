<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class HandleRequestService {

    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function sendPopUp( $uniqueId,  $callerId,  $destination)
    {
        return $this->sendRequest([
            'uniqueId' => $uniqueId,
            'callerId' => $callerId,
            'destination' => $destination,
        ]);
    }

    public function sendCdr($uniqueId,$callerId,$destination,$duration,$buildSeconds,$callStatus,$fileUrl){

        return $this->sendRequest([
            'uniqueId' => $uniqueId,
            'callerId' => $callerId,
            'destination' => $destination,
            'duration' => $duration,
            'duration' => $duration,
            'buildSeconds' => $buildSeconds,
            'callStatus' => $callStatus,
            'fileUrl' => $fileUrl,
        ]);
    }


    public function sendRequest(array $data){
        $url = env('BASEURL') . "/api/asanito/voip/call";

    try {
        $response = $this->client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'ApiKey' => env('APIKEY'),
            ],
            'json' => $data,
        ]);

        return json_decode($response->getBody(), true);
    } catch (RequestException $e) {
        throw new \Exception('Send Request Failed: ' . $e->getMessage());
    }
    }


}



