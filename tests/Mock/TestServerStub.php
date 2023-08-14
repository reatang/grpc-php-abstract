<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Reatang\GrpcPHPAbstract\Tests\Mock;

/**
 */
class TestServerStub {

    /**
     * @param \Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingResponse for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function Ping(
        \Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingRequest $request,
        \Grpc\ServerContext $context
    ): ?\Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingResponse {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \Reatang\GrpcPHPAbstract\Tests\Mock\PB\OtelRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \Reatang\GrpcPHPAbstract\Tests\Mock\PB\OtelResponse for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function Otel(
        \Reatang\GrpcPHPAbstract\Tests\Mock\PB\OtelRequest $request,
        \Grpc\ServerContext $context
    ): ?\Reatang\GrpcPHPAbstract\Tests\Mock\PB\OtelResponse {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * Get the method descriptors of the service for server registration
     *
     * @return array of \Grpc\MethodDescriptor for the service methods
     */
    public final function getMethodDescriptors(): array
    {
        return [
            '/reatang.grpc_php_abstract.tests.mock.TestServer/Ping' => new \Grpc\MethodDescriptor(
                $this,
                'Ping',
                '\Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/reatang.grpc_php_abstract.tests.mock.TestServer/Otel' => new \Grpc\MethodDescriptor(
                $this,
                'Otel',
                '\Reatang\GrpcPHPAbstract\Tests\Mock\PB\OtelRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
        ];
    }

}
