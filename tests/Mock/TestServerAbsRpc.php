<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Reatang\GrpcPHPAbstract\Tests\Mock;

use Reatang\GrpcPHPAbstract\Client\GrpcBaseClient;

// 请求参数
use \Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingRequest;
use \Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingResponse;
use \Reatang\GrpcPHPAbstract\Tests\Mock\PB\OTelRequest;
use \Reatang\GrpcPHPAbstract\Tests\Mock\PB\OTelResponse;

/**
 * @property TestServerClient $client
 *
 * @method PingResponse Ping(PingRequest $request, array $opts = [])
 * @method OTelResponse OTel(OTelRequest $request, array $opts = [])
 *
 */
class TestServerAbsRpc extends GrpcBaseClient
{
    public function __construct($host, array $interceptors = [], array $options = [])
    {
        parent::__construct($host, TestServerClient::class, $interceptors, $options);
    }
}