<?php

namespace Notifier\Bridge\AllMySMS\Tests;

use Notifier\Bridge\AllMySMS\AllMySMSTransport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class AllMySMSTransportTest extends TestCase
{
    /**
     * @var MockObject|HttpClientInterface
     */
    private $httpClient;

    /**
     * @var AllMySMSTransport
     */
    private $transport;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $tpoa;

    public function setUp(): void
    {
        $this->login = 'login';
        $this->apiKey = 'apiKey';
        $this->tpoa = 'test';

        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->transport = new AllMySMSTransport($this->login, $this->apiKey, $this->tpoa, $this->httpClient);
    }

    public function test_transport(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $response->expects($this->once())->method('getStatusCode')->willReturn(201);

        $this->transport->send(new SmsMessage('+33612345678', 'Test Message'));
    }

    public function test_to_string(): void
    {
        $this->assertSame('allmysms://localhost?from=' . $this->tpoa, $this->transport->__toString());
    }

    public function test_supports(): void
    {
        $this->assertTrue($this->transport->supports(new SmsMessage('+33612345678', 'Test Message')));
        $this->assertFalse($this->transport->supports($this->createMock(MessageInterface::class)));
    }

    public function test_send_fail(): void
    {
        $this->expectException(LogicException::class);
        $this->transport->send($this->createMock(MessageInterface::class));
    }

    public function test_transport_fail(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('Unable to send the SMS: Invalid sender (400)');
        $response = $this->createMock(ResponseInterface::class);
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $response->expects($this->once())->method('getStatusCode')->willReturn(400);
        $response->expects($this->once())->method('toArray')->willReturn([
            'description' => 'Invalid sender',
            'code' => 400
        ]);

        $this->transport->send(new SmsMessage('+33612345678', 'Test Message'));
    }
}
