<?php

namespace App\Http\Controllers;

use App\Services\EthereumService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class BalanceController extends Controller
{
    public function __construct(
        protected EthereumService $ethereum
    ) {}

    public function show(string $wallet): JsonResponse
    {
        try {
            $balance = Cache::remember("balance:{$wallet}", 15, function () use ($wallet) {
                return $this->ethereum->getBalance($wallet);
            });
            
            return response()->json([
                'wallet' => $wallet,
                'balance_eth' => (float) $balance,
                'cached' => Cache::has("balance:{$wallet}")
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch balance'], 500);
        }
    }

    public function tokenBalance(string $wallet, string $token): JsonResponse
    {
        try {
            $decimals = strcasecmp($token, '0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48') === 0 ? 6 : 18;
            
            $cacheKey = "token_balance:{$wallet}:{$token}";
            $balance = Cache::remember($cacheKey, 15, fn() => 
                $this->ethereum->getTokenBalance($wallet, $token, $decimals)
            );

            return response()->json([
                'wallet' => $wallet,
                'token_contract' => $token,
                'balance' => (float) $balance,
                'decimals' => $decimals
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}