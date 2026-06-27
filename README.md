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
```bash
curl http://localhost:8000/balance/0xd8dA6BF26964aF9D7eEd9e03E53415D37aA96045

{
  "wallet": "0xd8dA6BF26964aF9D7eEd9e03E53415D37aA96045",
  "balance_eth": 6423.4458,
  "cached": true
} ```
### **API Demo**
