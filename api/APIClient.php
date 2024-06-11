<?php

namespace api;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
class APIClient
{
    const API_URL = 'https://pro-api.coinmarketcap.com/v1/';
    const API_KEY = '9936a977-e516-446d-891c-788a396489df';

    public function getApiData(string $endpoint, array $params = []): ?array
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

    public function topCryptos(int $limit = 10): void
    {
        $cryptos = $this->getApiData('cryptocurrency/listings/latest', ['limit' => $limit, 'convert' => 'USD']);
        if ($cryptos !== null) {
            $output = new ConsoleOutput();
            $table = new Table($output);
            $headers = [
                '<fg=red;options=bold>Rank</>',
                '<fg=red;options=bold>Symbol</>',
                '<fg=red;options=bold>Name</>',
                '<fg=red;options=bold>Price (USD)</>'
            ];
            $table->setHeaders($headers);

            foreach ($cryptos as $crypto) {
                $table->addRow([
                    $crypto['cmc_rank'],
                    $crypto['symbol'],
                    $crypto['name'],
                    '$' . number_format($crypto['quote']['USD']['price'], 2),
                ]);
            }

            $table->render();
        } else {
            echo "Failed to fetch cryptocurrency data.\n";
        }
    }

    public function cryptoBySymbol(string $symbol): ?array
    {
        $data = $this->getCryptoData($symbol);
        if ($data !== null) {
            return $data[$symbol];
        } else {
            echo "Failed to fetch cryptocurrency data for symbol $symbol.\n";
            return null;
        }
    }

    public function displayCryptoData(string $symbol): void
    {
        $data = $this->getCryptoData($symbol);
        if ($data !== null) {
            $output = new ConsoleOutput();
            $table = new Table($output);
            $headers = [
                '<fg=red;options=bold>Symbol</>',
                '<fg=red;options=bold>Name</>',
                '<fg=red;options=bold>Price (USD)</>'
            ];
            $table->setHeaders($headers);

            $cryptoData = $data[$symbol];
            $table->addRow([
                $cryptoData['symbol'],
                $cryptoData['name'],
                '$' . number_format($cryptoData['quote']['USD']['price'], 2),
            ]);

            $table->render();
        } else {
            echo "Failed to fetch cryptocurrency data for symbol $symbol.\n";
        }
    }

    private function getCryptoData(string $symbol): array
    {
        return $this->getApiData('cryptocurrency/quotes/latest', ['symbol' => $symbol, 'convert' => 'USD']);
    }

}