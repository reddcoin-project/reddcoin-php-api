<?php

namespace IndieBlock\Reddcoin\Tests;

use IndieBlock\Reddcoin\Client as ReddcoinClient;
use IndieBlock\Reddcoin\Exceptions;
use IndieBlock\Reddcoin\Responses\ReddcoindResponse;
use IndieBlock\Reddcoin\Responses\Response;
use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

class ClientTest extends TestCase
{
    /**
     * Set-up test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->reddcoind = new ReddcoinClient();
    }

    /**
     * Test url parser.
     *
     * @param string $url
     * @param string $scheme
     * @param string $host
     * @param int    $port
     * @param string $user
     * @param string $password
     *
     * @return void
     *
     * @dataProvider urlProvider
     */
    public function testUrlParser($url, $scheme, $host, $port, $user, $password)
    {
        $reddcoind = new ReddcoinClient($url);

        $this->assertInstanceOf(ReddcoinClient::class, $reddcoind);

        $base_uri = $reddcoind->getClient()->getConfig('base_uri');

        $this->assertEquals($base_uri->getScheme(), $scheme);
        $this->assertEquals($base_uri->getHost(), $host);
        $this->assertEquals($base_uri->getPort(), $port);

        $auth = $reddcoind->getClient()->getConfig('auth');
        $this->assertEquals($auth[0], $user);
        $this->assertEquals($auth[1], $password);
    }

    /**
     * Data provider for url expander test.
     *
     * @return array
     */
    public function urlProvider()
    {
        return [
            ['https://localhost', 'https', 'localhost', 8332, '', ''],
            ['https://localhost:8000', 'https', 'localhost', 8000, '', ''],
            ['http://localhost', 'http', 'localhost', 8332, '', ''],
            ['http://localhost:8000', 'http', 'localhost', 8000, '', ''],
            ['http://testuser@127.0.0.1:8000/', 'http', '127.0.0.1', 8000, 'testuser', ''],
            ['http://testuser:testpass@localhost:8000', 'http', 'localhost', 8000, 'testuser', 'testpass'],
        ];
    }

    /**
     * Test client config getter.
     *
     * @return void
     */
    public function testClientConfigGetter()
    {
        $config = $this->reddcoind->getConfig();

        $this->assertNull($this->reddcoind->getConfig('nonexistent'));

        $this->assertSame($config['scheme'], $this->reddcoind->getConfig('scheme'));
        $this->assertSame($config['host'], $this->reddcoind->getConfig('host'));
        $this->assertSame($config['port'], $this->reddcoind->getConfig('port'));
        $this->assertSame($config['user'], $this->reddcoind->getConfig('user'));
        $this->assertSame($config['password'], $this->reddcoind->getConfig('password'));
        $this->assertSame($config['ca'], $this->reddcoind->getConfig('ca'));
        $this->assertSame($config['preserve_case'], $this->reddcoind->getConfig('preserve_case'));
    }

    /**
     * Test url parser with invalid url.
     *
     * @return array
     */
    public function testUrlParserWithInvalidUrl()
    {
        $this->expectException(Exceptions\BadConfigurationException::class);
        $this->expectExceptionMessage('Invalid url');

        $reddcoind = new ReddcoinClient('cookies!');
    }

    /**
     * Test client getter and setter.
     *
     * @return void
     */
    public function testClientSetterGetter()
    {
        $reddcoind = new ReddcoinClient('http://old_client.org');
        $this->assertInstanceOf(ReddcoinClient::class, $reddcoind);

        $base_uri = $reddcoind->getClient()->getConfig('base_uri');
        $this->assertEquals($base_uri->getHost(), 'old_client.org');

        $oldClient = $reddcoind->getClient();
        $this->assertInstanceOf(GuzzleHttp::class, $oldClient);

        $newClient = new GuzzleHttp(['base_uri' => 'http://new_client.org']);
        $reddcoind->setClient($newClient);

        $base_uri = $reddcoind->getClient()->getConfig('base_uri');
        $this->assertEquals($base_uri->getHost(), 'new_client.org');
    }

    /**
     * Test ca config option.
     *
     * @return void
     */
    public function testCaOption()
    {
        $reddcoind = new ReddcoinClient();

        $this->assertEquals(null, $reddcoind->getClient()->getConfig('ca'));

        $reddcoind = new ReddcoinClient([
            'ca' => __FILE__,
        ]);

        $this->assertEquals(__FILE__, $reddcoind->getClient()->getConfig('verify'));
    }

    /**
     * Test preserve method name case config option.
     *
     * @return void
     */
    public function testPreserveCaseOption()
    {
        $reddcoind = new ReddcoinClient(['preserve_case' => true]);
        $reddcoind->setClient($this->mockGuzzle([$this->getBlockResponse()]));
        $reddcoind->getBlockHeader();

        $request = $this->getHistoryRequestBody();

        $this->assertEquals($this->makeRequestBody(
            'getBlockHeader',
            $request['id']
        ), $request);
    }

    /**
     * Test simple request.
     *
     * @return void
     */
    public function testRequest()
    {
        $response = $this->reddcoind
            ->setClient($this->mockGuzzle([$this->getBlockResponse()]))
            ->request(
                'getblockheader',
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
            );

        $request = $this->getHistoryRequestBody();

        $this->assertEquals($this->makeRequestBody(
            'getblockheader',
            $request['id'],
            '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
        ), $request);
        $this->assertEquals(self::$getBlockResponse, $response->get());
    }

    /**
     * Test multiwallet request.
     *
     * @return void
     */
    public function testMultiWalletRequest()
    {
        $wallet = 'testwallet.dat';

        $response = $this->reddcoind
            ->setClient($this->mockGuzzle([$this->getBalanceResponse()]))
            ->wallet($wallet)
            ->request('getbalance');

        $this->assertEquals(self::$balanceResponse, $response->get());
        $this->assertEquals(
            $this->getHistoryRequestUri()->getPath(),
            "/wallet/$wallet"
        );
    }

    /**
     * Test async multiwallet request.
     *
     * @return void
     */
    public function testMultiWalletAsyncRequest()
    {
        $wallet = 'testwallet2.dat';

        $this->reddcoind
            ->setClient($this->mockGuzzle([$this->getBalanceResponse()]))
            ->wallet($wallet)
            ->requestAsync('getbalance', []);

        $this->reddcoind->__destruct();

        $this->assertEquals(
            $this->getHistoryRequestUri()->getPath(),
            "/wallet/$wallet"
        );
    }

    /**
     * Test async request.
     *
     * @return void
     */
    public function testAsyncRequest()
    {
        $onFulfilled = $this->mockCallable([
            $this->callback(function (ReddcoindResponse $response) {
                return $response->get() == self::$getBlockResponse;
            }),
        ]);

        $this->reddcoind
            ->setClient($this->mockGuzzle([$this->getBlockResponse()]))
            ->requestAsync(
                'getblockheader',
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
                function ($response) use ($onFulfilled) {
                    $onFulfilled($response);
                }
            );

        $this->reddcoind->__destruct();

        $request = $this->getHistoryRequestBody();
        $this->assertEquals($this->makeRequestBody(
            'getblockheader',
            $request['id'],
            '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
        ), $request);
    }

    /**
     * Test magic request.
     *
     * @return void
     */
    public function testMagic()
    {
        $response = $this->reddcoind
            ->setClient($this->mockGuzzle([$this->getBlockResponse()]))
            ->getBlockHeader(
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
            );

        $request = $this->getHistoryRequestBody();
        $this->assertEquals($this->makeRequestBody(
            'getblockheader',
            $request['id'],
            '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
        ), $request);
    }

    /**
     * Test magic request.
     *
     * @return void
     */
    public function testAsyncMagic()
    {
        $onFulfilled = $this->mockCallable([
            $this->callback(function (ReddcoindResponse $response) {
                return $response->get() == self::$getBlockResponse;
            }),
        ]);

        $this->reddcoind
            ->setClient($this->mockGuzzle([$this->getBlockResponse()]))
            ->getBlockHeaderAsync(
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
                function ($response) use ($onFulfilled) {
                    $onFulfilled($response);
                }
            );

        $this->reddcoind->__destruct();

        $request = $this->getHistoryRequestBody();
        $this->assertEquals($this->makeRequestBody(
            'getblockheader',
            $request['id'],
            '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
        ), $request);
    }

    /**
     * Test reddcoind exception.
     *
     * @return void
     */
    public function testReddcoindException()
    {
        $this->expectException(Exceptions\BadRemoteCallException::class);
        $this->expectExceptionMessage(self::$rawTransactionError['message']);
        $this->expectExceptionCode(self::$rawTransactionError['code']);

        $this->reddcoind
            ->setClient($this->mockGuzzle([$this->rawTransactionError(200)]))
            ->getRawTransaction(
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
            );
    }

    /**
     * Test request exception with error code.
     *
     * @return void
     */
    public function testRequestExceptionWithServerErrorCode()
    {
        $this->expectException(Exceptions\BadRemoteCallException::class);
        $this->expectExceptionMessage(self::$rawTransactionError['message']);
        $this->expectExceptionCode(self::$rawTransactionError['code']);

        $this->reddcoind
            ->setClient($this->mockGuzzle([$this->rawTransactionError(200)]))
            ->getRawTransaction(
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
            );
    }

    /**
     * Test request exception with empty response body.
     *
     * @return void
     */
    public function testRequestExceptionWithEmptyResponseBody()
    {
        $this->expectException(Exceptions\ConnectionException::class);
        $this->expectExceptionMessage($this->error500());
        $this->expectExceptionCode(500);

        $this->reddcoind
            ->setClient($this->mockGuzzle([new GuzzleResponse(500)]))
            ->getRawTransaction(
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
            );
    }

    /**
     * Test async request exception with empty response body.
     *
     * @return void
     */
    public function testAsyncRequestExceptionWithEmptyResponseBody()
    {
        $rejected = $this->mockCallable([
            $this->callback(function (Exceptions\ClientException $exception) {
                return $exception->getMessage() == $this->error500() &&
                    $exception->getCode() == 500;
            }),
        ]);

        $this->reddcoind
            ->setClient($this->mockGuzzle([new GuzzleResponse(500)]))
            ->requestAsync(
                'getrawtransaction',
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
                null,
                function ($exception) use ($rejected) {
                    $rejected($exception);
                }
            );

        $this->reddcoind->__destruct();
    }

    /**
     * Test request exception with response.
     *
     * @return void
     */
    public function testRequestExceptionWithResponseBody()
    {
        $this->expectException(Exceptions\BadRemoteCallException::class);
        $this->expectExceptionMessage(self::$rawTransactionError['message']);
        $this->expectExceptionCode(self::$rawTransactionError['code']);

        $this->reddcoind
            ->setClient($this->mockGuzzle([$this->requestExceptionWithResponse()]))
            ->getRawTransaction(
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
            );
    }

    /**
     * Test async request exception with response.
     *
     * @return void
     */
    public function testAsyncRequestExceptionWithResponseBody()
    {
        $onRejected = $this->mockCallable([
            $this->callback(function (Exceptions\BadRemoteCallException $exception) {
                return $exception->getMessage() == self::$rawTransactionError['message'] &&
                    $exception->getCode() == self::$rawTransactionError['code'];
            }),
        ]);

        $this->reddcoind
            ->setClient($this->mockGuzzle([$this->requestExceptionWithResponse()]))
            ->requestAsync(
                'getrawtransaction',
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
                null,
                function ($exception) use ($onRejected) {
                    $onRejected($exception);
                }
            );

        $this->reddcoind->__destruct();
    }

    /**
     * Test request exception with no response.
     *
     * @return void
     */
    public function testRequestExceptionWithNoResponseBody()
    {
        $this->expectException(Exceptions\ClientException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $this->reddcoind
            ->setClient($this->mockGuzzle([$this->requestExceptionWithoutResponse()]))
            ->getRawTransaction(
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
            );
    }

    /**
     * Test async request exception with no response.
     *
     * @return void
     */
    public function testAsyncRequestExceptionWithNoResponseBody()
    {
        $rejected = $this->mockCallable([
            $this->callback(function (Exceptions\ClientException $exception) {
                return $exception->getMessage() == 'test' &&
                    $exception->getCode() == 0;
            }),
        ]);

        $this->reddcoind
            ->setClient($this->mockGuzzle([$this->requestExceptionWithoutResponse()]))
            ->requestAsync(
                'getrawtransaction',
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
                null,
                function ($exception) use ($rejected) {
                    $rejected($exception);
                }
            );

        $this->reddcoind->__destruct();
    }

    /**
     * Test setting different response handler class.
     *
     * @return void
     */
    public function testSetResponseHandler()
    {
        $fake = new FakeClient();

        $guzzle = $this->mockGuzzle([
            $this->getBlockResponse(),
        ], $fake->getClient()->getConfig('handler'));

        $response = $fake
            ->setClient($guzzle)
            ->request(
                'getblockheader',
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
            );

        $this->assertInstanceOf(FakeResponse::class, $response);
    }
}

class FakeClient extends ReddcoinClient
{
    /**
     * Gets response handler class name.
     *
     * @return string
     */
    protected function getResponseHandler() : string
    {
        return 'IndieBlock\\Reddcoin\\Tests\\FakeResponse';
    }
}

class FakeResponse extends Response
{
    //
}
