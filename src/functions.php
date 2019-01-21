<?php

declare(strict_types=1);

namespace IndieBlock\Reddcoin;

use IndieBlock\Reddcoin\Exceptions\Handler as ExceptionHandler;

if (!function_exists('to_reddcoin')) {
    /**
     * Converts from reddoshi to reddcoin.
     *
     * @param int $reddoshi
     *
     * @return string
     */
    function to_reddcoin(int $reddoshi) : string
    {
        return bcdiv((string) $reddoshi, (string) 1e8, 8);
    }
}

if (!function_exists('to_reddoshi')) {
    /**
     * Converts from reddcoin to reddoshi.
     *
     * @param string|float $reddcoin
     *
     * @return string
     */
    function to_reddoshi($reddcoin) : string
    {
        return bcmul(to_fixed((float) $reddcoin, 8), (string) 1e8);
    }
}

if (!function_exists('to_urdd')) {
    /**
     * Converts from reddcoin to urdd/bits.
     *
     * @param string|float $reddcoin
     *
     * @return string
     */
    function to_urdd($reddcoin) : string
    {
        return bcmul(to_fixed((float) $reddcoin, 8), (string) 1e6, 4);
    }
}

if (!function_exists('to_mrdd')) {
    /**
     * Converts from reddcoin to mrdd.
     *
     * @param string|float $reddcoin
     *
     * @return string
     */
    function to_mrdd($reddcoin) : string
    {
        return bcmul(to_fixed((float) $reddcoin, 8), (string) 1e3, 4);
    }
}

if (!function_exists('to_fixed')) {
    /**
     * Brings number to fixed precision without rounding.
     *
     * @param float $number
     * @param int   $precision
     *
     * @return string
     */
    function to_fixed(float $number, int $precision = 8) : string
    {
        $number = $number * pow(10, $precision);

        return bcdiv((string) $number, (string) pow(10, $precision), $precision);
    }
}

if (!function_exists('exception')) {
    /**
     * Gets exception handler instance.
     *
     * @return \IndieBlock\Reddcoin\Exceptions\Handler
     */
    function exception() : ExceptionHandler
    {
        return ExceptionHandler::getInstance();
    }
}

set_exception_handler([ExceptionHandler::getInstance(), 'handle']);
