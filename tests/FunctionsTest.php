<?php

namespace IndieBlock\Reddcoin\Tests;

use IndieBlock\Reddcoin;
use IndieBlock\Reddcoin\Exceptions\Handler as ExceptionHandler;

class FunctionsTest extends TestCase
{
    /**
     * Test satoshi to rdd converter.
     *
     * @param int    $satoshi
     * @param string $reddcoin
     *
     * @return void
     *
     * @dataProvider satoshiBtcProvider
     */
    public function testToBtc($satoshi, $reddcoin)
    {
        $this->assertEquals($reddcoin, Reddcoin\to_reddcoin($satoshi));
    }

    /**
     * Test reddcoin to satoshi converter.
     *
     * @param int    $satoshi
     * @param string $reddcoin
     *
     * @return void
     *
     * @dataProvider satoshiBtcProvider
     */
    public function testToSatoshi($satoshi, $reddcoin)
    {
        $this->assertEquals($satoshi, Reddcoin\to_satoshi($reddcoin));
    }

    /**
     * Test reddcoin to urdd/bits converter.
     *
     * @param int    $urdd
     * @param string $reddcoin
     *
     * @return void
     *
     * @dataProvider bitsBtcProvider
     */
    public function testToBits($urdd, $reddcoin)
    {
        $this->assertEquals($urdd, Reddcoin\to_urdd($reddcoin));
    }

    /**
     * Test reddcoin to mrdd converter.
     *
     * @param float  $mrdd
     * @param string $reddcoin
     *
     * @return void
     *
     * @dataProvider mrddBtcProvider
     */
    public function testToMrdd($mrdd, $reddcoin)
    {
        $this->assertEquals($mrdd, Reddcoin\to_mrdd($reddcoin));
    }

    /**
     * Test float to fixed converter.
     *
     * @param float  $float
     * @param int    $precision
     * @param string $expected
     *
     * @return void
     *
     * @dataProvider floatProvider
     */
    public function testToFixed($float, $precision, $expected)
    {
        $this->assertSame($expected, Reddcoin\to_fixed($float, $precision));
    }

    /**
     * Test exception handler helper.
     *
     * @return void
     */
    public function testExceptionHandlerHelper()
    {
        $this->assertInstanceOf(ExceptionHandler::class, Reddcoin\exception());
    }

    /**
     * Provides satoshi and reddcoin values.
     *
     * @return array
     */
    public function satoshiBtcProvider()
    {
        return [
            [1000, '0.00001000'],
            [2500, '0.00002500'],
            [-1000, '-0.00001000'],
            [100000000, '1.00000000'],
            [150000000, '1.50000000'],
        ];
    }

    /**
     * Provides satoshi and urdd/bits values.
     *
     * @return array
     */
    public function bitsBtcProvider()
    {
        return [
            [10, '0.00001000'],
            [25, '0.00002500'],
            [-10, '-0.00001000'],
            [1000000, '1.00000000'],
            [1500000, '1.50000000'],
        ];
    }

    /**
     * Provides satoshi and mrdd values.
     *
     * @return array
     */
    public function mrddBtcProvider()
    {
        return [
            [0.01, '0.00001000'],
            [0.025, '0.00002500'],
            [-0.01, '-0.00001000'],
            [1000, '1.00000000'],
            [1500, '1.50000000'],
        ];
    }

    /**
     * Provides float values with precision and result.
     *
     * @return array
     */
    public function floatProvider()
    {
        return [
            [1.2345678910, 0, '1'],
            [1.2345678910, 2, '1.23'],
            [1.2345678910, 4, '1.2345'],
            [1.2345678910, 8, '1.23456789'],
        ];
    }
}
