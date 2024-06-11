<?php

namespace api;
class APIClient
{
    const API_URL = 'https://pro-api.coinmarketcap.com/v1/';
    const API_KEY = '9936a977-e516-446d-891c-788a396489df';

    public function getApiData($endpoint, $params = [])
    {
        $url = self::API_URL . $endpoint . '?' . http_build_query($params);
        $headers = [
            'Accepts: application/json',
            'X-CMC_PRO_API_KEY: ' . self::API_KEY,
        ];
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($http_code !== 200) {
            echo "Error: Unable to fetch data. HTTP Status Code: $http_code\n";
            return null;
        }

        $data = json_decode($response, true);
        if (!isset($data['data'])) {
            echo "Error: Unexpected API response format\n";
            return null;
        }

        return $data['data'];
    }

    public function topCryptos($limit = 10)
    {
        return $this->getApiData('cryptocurrency/listings/latest', ['limit' => $limit, 'convert' => 'USD']);
    }

    public function cryptoBySymbol($symbol)
    {
        $data = $this->getApiData('cryptocurrency/quotes/latest', ['symbol' => $symbol, 'convert' => 'USD']);
        if (isset($data[$symbol])) {
            return $data[$symbol];
        } else {
            echo "Error: Cryptocurrency not found\n";
            return null;
        }
    }
}