<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EthereumService
{
    protected string $rpcUrl;

    public function __construct()
    {
        $this->rpcUrl = config('services.alchemy.rpc_url');
        
        if (empty($this->rpcUrl)) {
            throw new \RuntimeException('ALCHEMY_RPC_URL not set in .env or phpunit.xml');
        }
    }

    public function getBalance(string $address): float
    {
        if (!$this->isValidAddress($address)) {
            throw new \InvalidArgumentException('Invalid Ethereum address');
        }

        $response = $this->rpcCall('eth_getBalance', [$address, 'latest']);
        $weiHex = $response['result'];
        
        return $this->hexToDec($weiHex) / 1e18;
    }

    public function getTokenBalance(string $wallet, string $tokenContract, int $decimals = 18): float
    {
        if (!$this->isValidAddress($wallet) || !$this->isValidAddress($tokenContract)) {
            throw new \InvalidArgumentException('Invalid address');
        }

        // balanceOf(address) = 0x70a08231
        $data = '0x70a08231' . str_pad(substr($wallet, 2), 64, '0', STR_PAD_LEFT);

        $response = $this->rpcCall('eth_call', [[
            'to' => $tokenContract,
            'data' => $data
        ], 'latest']);

        $balanceHex = $response['result'];
        $balance = $this->hexToDec($balanceHex);
        
        return $balance / pow(10, $decimals);
    }

    protected function rpcCall(string $method, array $params): array
    {
        $response = Http::timeout(10)->post($this->rpcUrl, [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => $method,
            'params' => $params
        ]);

        if ($response->failed()) {
            Log::error('RPC failed', ['status' => $response->status()]);
            throw new \Exception('Failed to connect to Ethereum RPC');
        }

        $data = $response->json();

        if (isset($data['error'])) {
            throw new \Exception("RPC Error: " . $data['error']['message']);
        }

        return $data;
    }

    /**
     * hexdec() overflows. This handles 32-byte values.
     */
    protected function hexToDec(string $hex): string
    {
        $hex = ltrim($hex, '0x');
        if ($hex === '' || $hex === '0') return '0';
        return base_convert($hex, 16, 10);
    }

    /**
     * Must be 0x + exactly 40 hex chars
     */
    public function isValidAddress(string $address): bool
    {
        return (bool) preg_match('/^0x[a-fA-F0-9]{40}$/', $address);
    }
}