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

    public function load(): array
    {
        $wallet = file_exists('wallet.json') ? json_decode(file_get_contents('wallet.json'), true) : ['USD' => self::STARTING_BALANCE];
        if (!isset($wallet['USD'])) {
            $wallet['USD'] = self::STARTING_BALANCE;
        }
        $transactions = file_exists('transactions.json') ? json_decode(file_get_contents('transactions.json'), true) : [];
        return [$wallet, $transactions];
    }

    private function save(): void
    {
        $walletObject = (object) $this->wallet;
        file_put_contents('wallet.json', json_encode($walletObject));
        file_put_contents('transactions.json', json_encode($this->transactions));
    }

    public function buyCrypto(array $crypto, float $amount): void
    {
        $symbol = $crypto['symbol'];
        $price = $crypto['quote']['USD']['price'];
        $cost = $amount * $price;

        $output = new ConsoleOutput();
        $table = new Table($output);

        $headers = [
            '<fg=red;options=bold>Transaction</>',
            '<fg=red;options=bold>Amount</>',
            '<fg=red;options=bold>Symbol</>',
            '<fg=red;options=bold>Cost</>'
        ];
        $table->setHeaders($headers);

        if ($this->wallet['USD'] >= $cost) {
            $this->wallet['USD'] -= $cost;
            $this->wallet[$symbol] = isset($this->wallet[$symbol]) ? $this->wallet[$symbol] + $amount : $amount;
            $this->transactions[] = ['type' => 'buy', 'symbol' => $symbol, 'amount' => $amount, 'price' => $price];
            $this->save();

            $table->addRow(['Buy', $amount, $symbol, '$' . $cost]);
            $table->render();

            echo "Bought $amount $symbol for \$$cost\n";
        } else {
            $table->addRow(['Failed Buy', $amount, $symbol, '$' . $cost]);
            $table->render();

            echo "Insufficient funds\n";
        }
    }

    public function sellCrypto(array $crypto, float $amount): void
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

        // Display transaction details in a table
        $output = new ConsoleOutput();
        $table = new Table($output);
        $headers = [
            '<fg=red;options=bold>Transaction</>',
            '<fg=red;options=bold>Amount</>',
            '<fg=red;options=bold>Symbol</>',
            '<fg=red;options=bold>Proceeds</>'
        ];
        $table->setHeaders($headers);
        $table->addRow(['Sell', $amount, $symbol, '$' . $proceeds]);
        $table->render();

        echo "Sold $amount $symbol for \$$proceeds\n";
    }

    public function showWallet(): void
    {
        $output = new ConsoleOutput();
        $table = new Table($output);
        $headers = [
            '<fg=red;options=bold>Currency</>',
            '<fg=red;options=bold>Amount</>'
        ];
        $table->setHeaders($headers);

        foreach ($this->wallet as $currency => $amount) {
            $table->addRow([$currency, $amount]);
        }

        $table->render();
    }

    public function transactions(): void
    {
        $output = new ConsoleOutput();
        $table = new Table($output);
        $headers = [
            '<fg=red;options=bold>Type</>',
            '<fg=red;options=bold>Currency</>',
            '<fg=red;options=bold>Amount</>',
            '<fg=red;options=bold>Price</>'
        ];
        $table->setHeaders($headers);

        foreach ($this->transactions as $transaction) {
            $table->addRow([$transaction['type'], $transaction['symbol'], $transaction['amount'], $transaction['price']]);
        }

        $table->render();

    }

    public function showCommands(): void
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