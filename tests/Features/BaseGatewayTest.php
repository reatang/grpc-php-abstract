<?php

namespace Reatang\GrpcPHPAbstract\Tests\Features;

use GuzzleHttp\RequestOptions;
use Reatang\GrpcPHPAbstract\Client\Options;
use Reatang\GrpcPHPAbstract\Metadata\Metadata;
use Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingRequest;
use Reatang\GrpcPHPAbstract\Tests\Mock\TestServer;
use Reatang\GrpcPHPAbstract\Tests\Mock\TestServerClient;
use Reatang\GrpcPHPAbstract\Tests\TestCase;

class BaseGatewayTest extends TestCase
{
    public function testPing()
    {
        $response = $this->getMockGatewayClient()->Ping(new PingRequest(["ping" => "test"]));
        $this->assertEquals($response->getPong(), "PONG");
    }

    public function testMetadata()
    {
        $metadata = "123";
        $response = $this->getMockGatewayClient()->Ping(new PingRequest(["ping" => "metadata"]), [
            Options::Metadata => Metadata::create(["abc" => $metadata]),
        ]);

        $this->assertEquals($response->getPong(), "PONG" . $metadata);
    }
}
