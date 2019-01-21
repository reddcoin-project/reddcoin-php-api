<?php

namespace IndieBlock\Reddcoin\Tests\Exceptions;

use IndieBlock\Reddcoin\Exceptions\BadRemoteCallException;
use IndieBlock\Reddcoin\Responses\Response;
use IndieBlock\Reddcoin\Tests\TestCase;

class BadRemoteCallExceptionTest extends TestCase
{
    /**
     * Set-up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->response = $this
            ->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->response
            ->expects($this->once())
            ->method('error')
            ->willReturn(['message' => 'Test message', 'code' => 1]);
    }

    /**
     * Test trowing exception.
     *
     * @return void
     */
    public function testThrow()
    {
        $this->expectException(BadRemoteCallException::class);
        $this->expectExceptionMessage('Test message');
        $this->expectExceptionCode(1);

        throw new BadRemoteCallException($this->response);
    }

    /**
     * Test response getter.
     *
     * @return void
     */
    public function testGetResponse()
    {
        $exception = new BadRemoteCallException($this->response);

        $this->assertInstanceOf(Response::class, $exception->getResponse());
    }

    /**
     * Test constructor parameters getter.
     *
     * @return void
     */
    public function testGetConstructionParameters()
    {
        $exception = new FakeBadRemoteCallException($this->response);

        $this->assertEquals(
            [
                $exception->getResponse(),
            ],
            $exception->getConstructorParameters()
        );
    }
}

class FakeBadRemoteCallException extends BadRemoteCallException
{
    public function getConstructorParameters() : array
    {
        return parent::getConstructorParameters();
    }
}
