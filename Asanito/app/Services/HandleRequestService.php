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

    public function send( $uniqueId,  $callerId,  $destination)
    {
        $url = env('BASEURL')."/api/asanito/voip/call";
        
        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'API-Key' => env('APIKEY'), 
                ],
                'json' => [
                    'uniqueId'    => $uniqueId,
                    'callerId'    => $callerId,
                    'destination' => $destination,
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new \Exception('Send Request Failed: ' . $e->getMessage());
        }
    }


}



