<?php

namespace Notifier\Bridge\AllMySMS\Tests;

use Notifier\Bridge\AllMySMS\AllMySMSTransport;
use Notifier\Bridge\AllMySMS\AllMySMSTransportFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AllMySMSTransportFactoryTest extends TestCase
{
    /**
     * @var MockObject|HttpClientInterface
     */
    private $httpClient;

    /**
     * @var AllMySMSTransportFactory
     */
    private $transportFactory;

    public function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->transportFactory = new AllMySMSTransportFactory();
    }

    public function test_create_from_string(): void
    {
        $dsn = 'allmysms://login:apiKey@localhost?from=test';
        $transport = $this->transportFactory->create(Dsn::fromString($dsn));
        $this->assertInstanceOf(AllMySMSTransport::class, $transport);
        $this->assertSame($dsn, $transport->__toString());
    }

    public function test_create_unsupported(): void
    {
        $this->expectException(UnsupportedSchemeException::class);
        $dsn = 'test://login:apiKey@localhost?from=test';
        $this->transportFactory->create(Dsn::fromString($dsn));
    }

    public function test_supports(): void
    {
        $this->assertTrue($this->transportFactory->supports(Dsn::fromString('allmysms://login:apiKey@localhost?from=test')));
        $this->assertFalse($this->transportFactory->supports(Dsn::fromString('test://login:apiKey@localhost?from=test')));
    }
}