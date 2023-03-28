<?php

namespace Reatang\GrpcPHPAbstract\Client;

use Grpc\AbstractCall;
use Grpc\BaseStub;
use Grpc\Interceptor;
use Reatang\GrpcPHPAbstract\Exceptions\ExceptionFunc;
use Reatang\GrpcPHPAbstract\Exceptions\GrpcException;
use Reatang\GrpcPHPAbstract\Metadata\GrpcHandle;
use Reatang\GrpcPHPAbstract\Metadata\Metadata;

abstract class GrpcBaseClient
{
    /** @var \Grpc\BaseStub */
    protected $client;

    /**
     * 获取grpc通道
     *
     * @param string $host
     * @param array  $opts
     * @param null   $interceptors
     *
     * @return array [string, array, \Grpc\Channel|\Grpc\Internal\InterceptorChannel]
     */
    protected function getChannel(string $host, array $opts, $interceptors = null): array
    {
        $c = BaseStub::getDefaultChannel($host, $opts);

        if ($interceptors) {
            return [$host, $opts, Interceptor::intercept($c, $interceptors)];
        } else {
            return [$host, $opts, $c];
        }
    }

    /**
     * 错误统一化
     *
     * @param object $status
     *
     * @return GrpcException
     */
    protected function exception(object $status): GrpcException
    {
        return ExceptionFunc::grpcException($status);
    }

    /**
     * metadata 获取统一化
     * @param AbstractCall $call
     *
     * @return Metadata
     */
    protected function metadata(AbstractCall $call): Metadata
    {
        return GrpcHandle::parseCall($call);
    }
}