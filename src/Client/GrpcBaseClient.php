<?php

namespace Reatang\GrpcPHPAbstract\Client;

use Grpc\AbstractCall;
use Reatang\GrpcPHPAbstract\Exceptions\ExceptionFunc;
use Reatang\GrpcPHPAbstract\Exceptions\GrpcException;
use Reatang\GrpcPHPAbstract\Metadata\GrpcHandle;
use Reatang\GrpcPHPAbstract\Metadata\Metadata;

abstract class GrpcBaseClient
{
    /** @var \Grpc\BaseStub */
    protected $client;

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