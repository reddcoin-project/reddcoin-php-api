# Reddcoin JSON-RPC PHP Client
- PHP 7.1 or higher


## Usage
Create a ReddCoin client object with a url parameter:
```
    use IndieBlockReddcoin\Client as ReddcoinClient;

    $redcoind = new ReddcoinClient('http://rpcuser:rpcpassword@localhost:8332/');
```
You can also use an array to define the `reddcoind` settings:

```
    use IndieBlockReddcoin\Client as ReddcoinClient;

    $reddcoind = new ReddcoinClient([
        'scheme'        => 'http',                 // optional, default http
        'host'          => 'localhost',            // optional, default localhost
        'port'          => 8332,                   // optional, default 8332
        'user'          => 'rpcuser',              // required
        'password'      => 'rpcpassword',          // required
        'ca'            => '/etc/ssl/ca-cert.pem'  // optional, for use with https scheme
        'preserve_case' => false,                  // optional, send method names as defined instead of lowercasing them
    ]);
```


You can call methods from the `reddcoind` API:
```
    /**
    * Get block info.
    */
    $block = $reddcoind->getBlock('000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f');

    $block('hash')->get();     // 000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f
    $block['height'];          // 0 (array access)
    $block->get('tx.0');       // 4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b
    $block->count('tx');       // 1
    $block->has('version');    // key must exist and CAN NOT be null
    $block->exists('version'); // key must exist and CAN be null
    $block->contains(0);       // check if response contains value
    $block->values();          // array of values
    $block->keys();            // array of keys
    $block->random(1, 'tx');   // random block txid
    $block('tx')->random(2);   // two random block txid's
    $block('tx')->first();     // txid of first transaction
    $block('tx')->last();      // txid of last transaction

    /**
    * Send transaction.
    */
    $result = $reddcoind->sendToAddress('mmXgiR6KAhZCyQ8ndr2BCfEq1wNG2UnyG6', 0.1);
    $txid = $result->get();

    /**
    * Get transaction amount.
    */
    $result = $reddcoind->listSinceBlock();
    $reddcoin = $result->sum('transactions.*.amount');
    $reddoshi = \IndieBlockReddcoin\to_reddoshi($reddcoin);
```
Asynchronous can also be made:
```
    $reddcoind->getBlockAsync(
        '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
        function ($response) {
            // success
        },
        function ($exception) {
            // error
        }
    );
```
## Multi-wallet support
```
    /**
    * Get the balance of 2 seperate wallets.
    */
    $balance1 = $reddcoind->wallet('wallet1.dat')->getbalance();
    $balance2 = $reddcoind->wallet('wallet2.dat')->getbalance();

    echo $balance1->get(); 
    echo $balance2->get(); 

```