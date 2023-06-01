<?php

namespace Reatang\GrpcPHPAbstract\Client;

use Grpc\AbstractCall;
use Grpc\BaseStub;
use Grpc\ChannelCredentials;
use Grpc\Interceptor;
use Psr\Log\LoggerInterface;
use Reatang\GrpcPHPAbstract\Exceptions\ExceptionFunc;
use Reatang\GrpcPHPAbstract\Exceptions\GrpcException;
use Reatang\GrpcPHPAbstract\Metadata\GrpcHandle;
use Reatang\GrpcPHPAbstract\Metadata\Metadata;
use Reatang\GrpcPHPAbstract\Middlewares\GrpcRetry;

abstract class GrpcBaseClient
{
    /** @var BaseStub */
    protected $client;

    /** @var LoggerInterface $logger */
    protected $logger = null;

    /** @var array $reConnectionParams reconnection need */
    private $reConnectionParams = [];

    public function __construct($host, $clientClassName, array $interceptors = [])
    {
        $this->initClient($clientClassName, $host, $interceptors);
    }

    /**
     * 自动初始化
     *
     * @param string     $clientClassName
     * @param string     $host
     * @param array|null $interceptors
     * @param bool       $forceNew
     *
     * @return void
     */
    protected function initClient(string $clientClassName, string $host, ?array $interceptors = [], bool $forceNew = false)
    {
        $this->reConnectionParams = func_get_args();

        if (empty($interceptors)) {
            $interceptors = [];
        }

        // auto retry interceptor
        $interceptors[] = (new GrpcRetry())->setLogger($this->logger);

        $this->client = new $clientClassName(...$this->getChannel($host, [
            'credentials' => ChannelCredentials::createInsecure(),
            'force_new' => $forceNew,
        ], $interceptors));
    }

    /**
     * 获取grpc通道
     *
     * @param string $host
     * @param array $opts
     * @param null $interceptors
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
     * 重连检测
     *
     * @param object $status
     *
     * @return bool
     */
    private function checkReconnection(object $status)
    {
        return isset($status->code) && $status->code == \Grpc\STATUS_UNAVAILABLE &&
            ($this->client->getConnectivityState(true) >= \Grpc\CHANNEL_TRANSIENT_FAILURE ||
                strpos($status->details, 'failed to connect') !== false
            );
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
     * 返回原生的grpc调用结果
     *
     * @param $method
     * @param $arguments
     *
     * @return array
     */
    protected function rawCall($method, $arguments)
    {
        $call = call_user_func_array([$this->client, ucfirst($method)], $arguments);

        return $call->wait();
    }

    /**
     * 代理调用的默认实现
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     * @throws GrpcException
     */
    public function __call($name, $arguments)
    {
        [$resp, $status] = $this->rawCall($name, $arguments);

        if ($this->checkReconnection($status) && !empty($this->reConnectionParams)) {
            $this->client->close();
            $this->reConnectionParams[3] = true;
            $this->initClient(...$this->reConnectionParams);

            // 重连
            [$resp, $status] = $this->rawCall($name, $arguments);
        }

        if ($status->code != 0) {
            throw $this->exception($status);
        }

        return $resp;
    }
}
