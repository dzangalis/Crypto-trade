<?php

require 'vendor/autoload.php';

use api\APIClient;
use api\Wallet;

$walletProcesses = new Wallet();
list($wallet, $transactions) = $walletProcesses->load();

while (true) {
    $walletProcesses->showCommands();
    $input = strtolower(readline("Please enter a command: \n"));


    switch ($input) {
        case '1':

            $apiClient = new APIClient();
            $apiClient->topCryptos();
            break;

        case '2':

            $symbol = strtoupper(readline("Enter cryptocurrency symbol: \n"));
            $apiClient = new APIClient();
            $apiClient->displayCryptoData($symbol);
            break;

        case '3':

            $apiClient = new APIClient();
            $symbol = strtoupper(readline("Enter cryptocurrency symbol: \n"));
            $amount = floatval(readline("Enter amount to buy: \n"));
            $crypto = $apiClient->cryptoBySymbol($symbol);
            if ($crypto !== null) {
                $walletProcesses->buyCrypto($crypto, $amount);
            }
            break;

        case '4':

            $apiClient = new APIClient();
            $symbol = strtoupper(readline("Enter cryptocurrency symbol: \n"));
            $amount = floatval(readline("Enter amount to sell: \n"));
            $crypto = $apiClient->cryptoBySymbol($symbol);
            if ($crypto !== null) {
                $walletProcesses->sellCrypto($crypto, $amount);
            }
            break;

        case '5':

            $walletProcesses->showWallet();
            break;

        case '6':

            $walletProcesses->transactions();
            break;

        case '7':

            exit;

        default:

            echo "Invalid choice\n";
    }
}