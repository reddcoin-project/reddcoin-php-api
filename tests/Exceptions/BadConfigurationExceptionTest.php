<?php

namespace IndieBlock\Reddcoin\Tests\Exceptions;

use IndieBlock\Reddcoin\Exceptions\BadConfigurationException;
use IndieBlock\Reddcoin\Tests\TestCase;

class BadConfigurationExceptionTest extends TestCase
{
    /**
     * Set-up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->config = ['test' => 'value'];
    }

    /**
     * Test trowing exception.
     *
     * @return void
     */
    public function testThrow()
    {
        $this->expectException(BadConfigurationException::class);
        $this->expectExceptionMessage('Test message');
        $this->expectExceptionCode(1);

        throw new BadConfigurationException($this->config, 'Test message', 1);
    }

    /**
     * Test config getter.
     *
     * @return void
     */
    public function testGetConfig()
    {
        $exception = new BadConfigurationException($this->config);

        $this->assertEquals($this->config, $exception->getConfig());
    }

    /**
     * Test constructor parameters getter.
     *
     * @return void
     */
    public function testGetConstructionParameters()
    {
        $exception = new FakeBadConfigurationException($this->config);

        $this->assertEquals(
            [
                $exception->getConfig(),
                $exception->getMessage(),
                $exception->getCode(),
            ],
            $exception->getConstructorParameters()
        );
    }
}

class FakeBadConfigurationException extends BadConfigurationException
{
    public function getConstructorParameters() : array
    {
        return parent::getConstructorParameters();
    }
}
