<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Reatang\GrpcPHPAbstract\Tests\Mock;

use Reatang\GrpcPHPAbstract\Client\GatewayBaseClient;

// 请求参数
use \Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingRequest;
use \Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingResponse;
use \Reatang\GrpcPHPAbstract\Tests\Mock\PB\OTelRequest;
use \Reatang\GrpcPHPAbstract\Tests\Mock\PB\OTelResponse;

/**
 * @method PingResponse Ping(PingRequest $request, array $opts = [])
 * @method OTelResponse OTel(OTelRequest $request, array $opts = [])
 *
 */
class TestServerGatewayRpc extends GatewayBaseClient
{
    public function __construct($host, array $interceptors = [])
    {
        parent::__construct($host, $interceptors);

        $this->addRoute("Ping", "/grpc/ping", PingResponse::class);
        $this->addRoute("OTel", "/grpc/otel", OTelResponse::class);
    }
}