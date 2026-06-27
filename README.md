# laravel-web3-rpc

Laravel 11 backend that queries Ethereum via Alchemy JSON-RPC. Get ETH + ERC20 balances with 15s Redis cache. Built without ethers.js — just PHP + RPC.

**Day 1-2 of 30-day Web3 backend sprint** for DeFi roles.

### **Features**

- `GET /balance/{wallet}` → ETH balance in ETH
- `GET /balance/{wallet}/{token}` → ERC20 token balance with decimals
- 15s cache via `Cache::remember` — cuts RPC costs 95%+
- Ethereum address validation with regex
- Pest tests using `Http::fake()` — no real RPC calls in CI
- Handles 256-bit hex values that break `hexdec()`

### **Tech Stack**

| Layer | Tech | Why |
| --- | --- | --- |
| **Backend** | Laravel 11, PHP 8.2 | Queues + Scheduler for indexing in Week 2 |
| **RPC** | Alchemy | Mainnet + testnets, generous free tier |
| **HTTP** | Laravel `Http` facade | Testable with `Http::fake()`, auto-retries |
| **Cache** | File/Redis | Prevents burning Alchemy compute units |
| **Testing** | PHPUnit/Pest | Mock all external calls |

### **API Demo**

**ETH Balance**
```
curl http://localhost:8000/balance/0xd8dA6BF26964aF9D7eEd9e03E53415D37aA96045

{
  "wallet": "0xd8dA6BF26964aF9D7eEd9e03E53415D37aA96045",
  "balance_eth": 6423.4458,
  "cached": true
}

```


### How It Works
1. JSON-RPC calls
POST to Alchemy with eth_getBalance and eth_call. No SDK needed.

2. Hex parsing
Chain returns hex. 0x1b1ae4d6e2ef500000 wei = 31 ETH. Uses base_convert() because hexdec() overflows on 32-byte values.

3. ERC20 via eth_call
balanceOf(address) selector = 0x70a08231. Pad wallet to 32 bytes, call token contract, parse result.

4. Caching
Cache::remember("balance:0x...", 15, fn() =>...) means 100 users checking same wallet in 15s = 1 RPC call. Alchemy charges $0.0003/call. This saves money.





