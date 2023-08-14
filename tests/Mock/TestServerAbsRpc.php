<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Reatang\GrpcPHPAbstract\Tests\Mock;

use Reatang\GrpcPHPAbstract\Client\GrpcBaseClient;

// 请求参数
use \Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingRequest;
use \Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingResponse;
use \Reatang\GrpcPHPAbstract\Tests\Mock\PB\OtelRequest;
use \Reatang\GrpcPHPAbstract\Tests\Mock\PB\OtelResponse;

/**
 * @property TestServerClient $client
 *
 * @method PingResponse Ping(PingRequest $request)
 * @method OtelResponse Otel(OtelRequest $request)
 *
 */
class TestServerAbsRpc extends GrpcBaseClient
{
    public function __construct($host, array $interceptors = [], array $options = [])
    {
        parent::__construct($host, TestServerClient::class, $interceptors, $options);
    }
}