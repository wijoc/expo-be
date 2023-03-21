<?php
namespace App\Services;

use GuzzleHttp\Client;

class RajaOngkirService
{
    protected $client;

    public function __construct () {
        $this->client = new Client([
            'base_uri' => 'https://api.rajaongkir.com/starter/',
            'headers' => [
                'key' => decrypt(config('app.ro_key'))
            ]
        ]);
    }

    public function getCities () {
        $response = $this->client->get('city');

        return json_decode($response->getBody(), true)['rajaongkir']['results'];
    }

    public function getCosts (Int $origin, Int $destination, Int $weight, String $courier) {
        try {
            $response = $this->client->request('POST', 'cost', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    'origin' => $origin,
                    'destination' => $destination,
                    'weight' => $weight,
                    'courier' => $courier,
                ]
            ]);
            return json_decode($response->getBody(), true)['rajaongkir'];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // return $e->getResponse()->getBody()->getContents();
            return json_decode($e->getResponse()->getBody(), true)['rajaongkir'];
        }
    }
}
