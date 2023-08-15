<?php

namespace Reatang\GrpcPHPAbstract\Tests\Features;

use Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingRequest;
use Reatang\GrpcPHPAbstract\Tests\Mock\TestServer;
use Reatang\GrpcPHPAbstract\Tests\Mock\TestServerClient;
use Reatang\GrpcPHPAbstract\Tests\TestCase;

class BaseTest extends TestCase
{
    public function testPing()
    {
        $response = $this->getMockClient()->Ping(new PingRequest(["ping" => "test"]));
        $this->assertEquals($response->getPong(), "PONG");
    }

    public function testMetadata()
    {
        $metadata = "123";
        /** @var TestServerClient $c */
        $c = $this->getMockClient()->rawClient();
        $call = $c->Ping(new PingRequest(["ping" => "metadata"]), [
            "abc" => [$metadata],
        ]);

        [$response, $status] = $call->wait();
        $this->assertEquals($status->code, 0);
        $this->assertEquals($response->getPong(), "PONG" . $metadata);
    }
}
