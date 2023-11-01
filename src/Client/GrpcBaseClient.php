<?php

namespace Reatang\GrpcPHPAbstract\Client;

use Grpc\AbstractCall;
use Grpc\BaseStub;
use Grpc\ChannelCredentials;
use Grpc\Interceptor;
use Psr\Log\LoggerInterface;
use Reatang\GrpcPHPAbstract\Exceptions\GrpcException;
use Reatang\GrpcPHPAbstract\Metadata\GrpcHandle;
use Reatang\GrpcPHPAbstract\Metadata\Metadata;
use Reatang\GrpcPHPAbstract\Middlewares\GrpcLogger;
use Reatang\GrpcPHPAbstract\Middlewares\GrpcRetry;
use Reatang\GrpcPHPAbstract\Utils\ExceptionFunc;
use Reatang\GrpcPHPAbstract\Utils\LoggerTrait;

abstract class GrpcBaseClient
{
    use LoggerTrait;

    /** @var BaseStub */
    protected $client;

    /** @var int ms */
    protected $timeout = 2000;

    /** @var array $reConnectionParams reconnection need */
    private $reConnectionParams = [];

    public function __construct($host, $clientClassName, array $interceptors = [], array $options = [])
    {
        if (isset($options['logger']) && $options['logger'] instanceof LoggerInterface) {
            $this->setLogger($options['logger']);
        }

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

        if ($this->logger) {
            $interceptors[] = new GrpcLogger($this->logger);
        }

        // auto retry interceptor
        $interceptors[] = (new GrpcRetry())->setLogger($this->logger);

        $this->client = new $clientClassName(...$this->getChannel($host, [
            'credentials' => ChannelCredentials::createInsecure(),
            'force_new' => $forceNew,
        ], $interceptors));
    }

    /**
     * 获取原始的Client
     *
     * @return BaseStub
     */
    public function rawClient()
    {
        return $this->client;
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
        if (!isset($arguments[1])) {
            $arguments[1] = [];
        }

        // timeout 单位：微秒
        if ($this->timeout > 0) {
            $arguments[2]['timeout'] = $this->timeout * 1000;
        }

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
