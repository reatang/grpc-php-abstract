<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: test_server.proto

namespace Reatang\GrpcPHPAbstract\Tests\Mock;

class TestServer
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        $pool->internalAddGeneratedFile(
            '
�
test_server.proto$reatang.grpc_php_abstract.tests.mock"
PingRequest
ping (	"
PingResponse
pong (	"
OtelRequest".
OtelResponse
trace (	
baggage (	2�

TestServerm
Ping1.reatang.grpc_php_abstract.tests.mock.PingRequest2.reatang.grpc_php_abstract.tests.mock.PingResponsem
Otel1.reatang.grpc_php_abstract.tests.mock.OtelRequest2.reatang.grpc_php_abstract.tests.mock.OtelResponseBS�PB\\�"Reatang\\GrpcPHPAbstract\\Tests\\Mock��"Reatang\\GrpcPHPAbstract\\Tests\\Mockbproto3'
        , true);

        static::$is_initialized = true;
    }
}

