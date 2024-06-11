<?php

namespace api;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class Wallet
{
    const STARTING_BALANCE = 1000;
    private $wallet;
    private $transactions;

    public function __construct()
    {
        list($this->wallet, $this->transactions) = $this->load();
    }

    public function load()
    {
        $wallet = file_exists('wallet.json') ? json_decode(file_get_contents('wallet.json'), true) : ['USD' => self::STARTING_BALANCE];
        if (!isset($wallet['USD'])) {
            $wallet['USD'] = self::STARTING_BALANCE;
        }
        $transactions = file_exists('transactions.json') ? json_decode(file_get_contents('transactions.json'), true) : [];
        return [$wallet, $transactions];
    }

    private function save()
    {
        file_put_contents('wallet.json', json_encode($this->wallet));
        file_put_contents('transactions.json', json_encode($this->transactions));
    }

    public function buyCrypto($crypto, $amount)
    {
        $symbol = $crypto['symbol'];
        $price = $crypto['quote']['USD']['price'];
        $cost = $amount * $price;

        echo "Available USD balance: {$this->wallet['USD']}\n";
        echo "Cost of purchase: $cost\n";

        if ($this->wallet['USD'] >= $cost) {
            $this->wallet['USD'] -= $cost;
            $this->wallet[$symbol] = isset($this->wallet[$symbol]) ? $this->wallet[$symbol] + $amount : $amount;
            $this->transactions[] = ['type' => 'buy', 'symbol' => $symbol, 'amount' => $amount, 'price' => $price];
            $this->save();

            echo "Bought $amount $symbol for \$$cost\n";
        } else {
            echo "Insufficient funds\n";
        }
    }

    public function sellCrypto($crypto, $amount)
    {
        $symbol = $crypto['symbol'];

        if (isset($this->wallet[$symbol]) === false || $this->wallet[$symbol] < $amount) {
            echo "Insufficient holdings\n";
            return;
        }

        $price = $crypto['quote']['USD']['price'];
        $proceeds = $amount * $price;
        $this->wallet['USD'] += $proceeds;
        $this->wallet[$symbol] -= $amount;

        if ($this->wallet[$symbol] == 0) {
            unset($this->wallet[$symbol]);
        }

        $this->transactions[] = ['type' => 'sell', 'symbol' => $symbol, 'amount' => $amount, 'price' => $price];
        $this->save();

        echo "Sold $amount $symbol for \$$proceeds\n";
    }

    public function showWallet()
    {
        echo "Wallet:\n";
        foreach ($this->wallet as $currency => $amount) {
            echo "$currency: $amount\n";
        }
    }

    public function transactions()
    {
        echo "Transactions:\n";
        foreach ($this->transactions as $records) {
            echo "{$records['type']} {$records['amount']} {$records['symbol']} at \${$records['price']}\n";
        }
    }

    public function showCommands()
    {
        {
            $output = new ConsoleOutput();
            $table = new Table($output);
            $headers = [
                '<fg=red;options=bold>Command</>',
                '<fg=red;options=bold>Description</>'
            ];
            $table->setHeaders($headers);
            $table->setRows([
                ['1. Top', 'List top cryptocurrencies.'],
                ['2. Search', 'Search cryptocurrency by symbol.'],
                ['3. Buy', 'Buy cryptocurrency.'],
                ['4. Sell', 'Sell cryptocurrency.'],
                ['5. Wallet', 'Display wallet.'],
                ['6. Transactions', 'Display transactions.'],
                ['7. Exit', 'Exit from the application']
            ]);
            $table->render();
        }
    }
}