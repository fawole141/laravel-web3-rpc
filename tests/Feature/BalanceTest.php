<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BalanceTest extends TestCase
{
    // 0x + 40 hex chars = valid address
    private string $testWallet = '0x000000000000000000000000000000000000dead';
    private string $usdc = '0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_returns_eth_balance(): void
    {
        Http::fake([
            '*' => Http::response([
                'jsonrpc' => '2.0',
                'id' => 1,
                'result' => '0x1b1ae4d6e2ef500000' // 31 ETH in wei
            ], 200)
        ]);

        $response = $this->get("/balance/{$this->testWallet}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'wallet' => $this->testWallet,
                     'balance_eth' => 31
                 ]);
    }

    public function test_returns_erc20_balance(): void
    {
        Http::fake([
            '*' => Http::response([
                'jsonrpc' => '2.0',
                'id' => 1,
                'result' => '0x3b9aca00' // 1000 USDC = 1000 * 10^6
            ], 200)
        ]);

        $response = $this->get("/balance/{$this->testWallet}/{$this->usdc}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'balance' => 1000,
                     'decimals' => 6
                 ]);
    }

    public function test_rejects_invalid_address(): void
    {
        $response = $this->get('/balance/0x123'); // Too short
        $response->assertStatus(400)
                 ->assertJson(['error' => 'Invalid Ethereum address']);
    }

    public function test_caches_balance(): void
    {
        Http::fake(['*' => Http::response(['result' => '0x1'])]); 
        
        $this->get("/balance/{$this->testWallet}")->assertStatus(200);
        $this->get("/balance/{$this->testWallet}")->assertStatus(200);
        
        Http::assertSentCount(1);
    }
}