<?php

namespace Reatang\GrpcPHPAbstract\Middlewares;

use Grpc\Interceptor;
use Grpc\UnaryCall;

/**
 * UnaryInterceptor
 */
class ExampleInterceptor extends Interceptor
{
    public function interceptUnaryUnary(
        $method,
        $argument,
        $deserialize,
        $continuation,
        array $metadata = [],
        array $options = []
    ) {
        /** @var UnaryCall $call */
        $call = $continuation($method, $argument, $deserialize, $metadata, $options);

        var_dump("ExampleInterceptor: " . json_encode($call->getMetadata()));

        return $call;
    }
}