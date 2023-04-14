<?php

namespace Reatang\GrpcPHPAbstract\Client;

use Grpc\AbstractCall;
use Grpc\BaseStub;
use Grpc\ChannelCredentials;
use Grpc\Interceptor;
use Reatang\GrpcPHPAbstract\Exceptions\ExceptionFunc;
use Reatang\GrpcPHPAbstract\Exceptions\GrpcException;
use Reatang\GrpcPHPAbstract\Metadata\GrpcHandle;
use Reatang\GrpcPHPAbstract\Metadata\Metadata;

abstract class GrpcBaseClient
{
    /** @var BaseStub */
    protected $client;

    /**
     * 自动初始化
     *
     * @param string $clientClassName
     * @param string $host
     * @param array  $interceptors
     *
     * @return void
     */
    protected function initClient(string $clientClassName, string $host, array $interceptors = [])
    {
        $this->client = new $clientClassName(...$this->getChannel($host, [
            'credentials' => ChannelCredentials::createInsecure(),
        ], $interceptors));
    }

    /**
     * 获取grpc通道
     *
     * @param string $host
     * @param array  $opts
     * @param null   $interceptors
     *
     * @return array [string, array, \Grpc\Channel|\Grpc\Internal\InterceptorChannel]
     */
    protected function getChannel(string $host, array $opts = [], $interceptors = null): array
    {
        if (empty($opts)) {
            $opts = [
                'credentials' => null,
            ];
        }

        $c = BaseStub::getDefaultChannel($host, $opts);

        if (!empty($interceptors)) {
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
     *
     * @param AbstractCall $call
     *
     * @return Metadata
     */
    protected function metadata(AbstractCall $call): Metadata
    {
        return GrpcHandle::parseCall($call);
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     * @throws GrpcException
     */
    public function __call($name, $arguments)
    {
        $call = call_user_func_array([$this->client, $name], $arguments);

        [$resp, $status] = $call->wait();

        if ($status->code != 0) {
            throw $this->exception($status);
        }

        return $resp;
    }
}
