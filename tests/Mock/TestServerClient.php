<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Reatang\GrpcPHPAbstract\Tests\Mock;

/**
 */
class TestServerClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function Ping(\Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/reatang.grpc_php_abstract.tests.mock.TestServer/Ping',
        $argument,
        ['\Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Reatang\GrpcPHPAbstract\Tests\Mock\PB\OTelRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function OTel(\Reatang\GrpcPHPAbstract\Tests\Mock\PB\OTelRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/reatang.grpc_php_abstract.tests.mock.TestServer/OTel',
        $argument,
        ['\Reatang\GrpcPHPAbstract\Tests\Mock\PB\OTelResponse', 'decode'],
        $metadata, $options);
    }

}
