<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: test_server.proto

namespace Reatang\GrpcPHPAbstract\Tests\Mock\PB;

/**
 * Protobuf type <code>reatang.grpc_php_abstract.tests.mock.TestServer</code>
 */
interface TestServerInterface
{
    /**
     * Method <code>ping</code>
     *
     * @param \Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingRequest $request
     * @return \Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingResponse
     */
    public function ping(\Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingRequest $request);

    /**
     * Method <code>oTel</code>
     *
     * @param \Reatang\GrpcPHPAbstract\Tests\Mock\PB\OTelRequest $request
     * @return \Reatang\GrpcPHPAbstract\Tests\Mock\PB\OTelResponse
     */
    public function oTel(\Reatang\GrpcPHPAbstract\Tests\Mock\PB\OTelRequest $request);

}
