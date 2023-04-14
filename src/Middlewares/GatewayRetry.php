<?php

namespace Reatang\GrpcPHPAbstract\Middlewares;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Reatang\GrpcPHPAbstract\Metadata\GatewayHandle;

class GatewayRetry
{
    /**
     * 最大重试次数
     */
    private int $maxAttempt;

    /**
     * 重试间隔时间，单位：毫秒ms
     * @var int
     */
    private int $delay;

    /** @var callable */
    private $nextHandler;

    public function __construct(
        int $maxAttempt,
        int $delay,
        callable $nextHandler
    ) {
        $this->maxAttempt = $maxAttempt;
        $this->delay = $delay;
        $this->nextHandler = $nextHandler;
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return PromiseInterface
     */
    public function __invoke(RequestInterface $request, array $options)
    {
        if (!isset($options['retries'])) {
            $options['retries'] = 0;
        }

        if ($options['retries'] > 0) {
            $request = $request->withHeader(GatewayHandle::MetadataHeaderPrefix . "x-retry-attempt", $options['retries']);
        }

        $fn = $this->nextHandler;
        return $fn($request, $options)
            ->then(
                $this->onFulfilled($request, $options),
                $this->onRejected($request, $options)
            );
    }

    /**
     * Execute fulfilled closure
     *
     * @return mixed
     */
    private function onFulfilled(RequestInterface $req, array $options)
    {
        return function ($value) use ($req, $options) {
            if (!$this->decider($options['retries'], $req, $value, null)) {
                return $value;
            }

            return $this->doRetry($req, $options, $value);
        };
    }

    /**
     * Execute rejected closure
     *
     * @return callable
     */
    private function onRejected(RequestInterface $req, array $options)
    {
        return function ($reason) use ($req, $options) {
            if (!$this->decider($options['retries'], $req, null, $reason)) {
                return \GuzzleHttp\Promise\rejection_for($reason);
            }

            return $this->doRetry($req, $options);
        };
    }

    private function doRetry(RequestInterface $request, array $options, ResponseInterface $response = null)
    {
        $numberOfRetries = ++$options['retries'];

        // 计算延迟
        $options['delay'] = $this->delay + $numberOfRetries * 100;

        return $this($request, $options);
    }

    private function decider($retries, RequestInterface $request, Response $response = null, \Exception $exception = null)
    {
        // 超过最大重试次数，不再重试
        if ($retries >= $this->maxAttempt) {
            return false;
        }

        // 请求失败，继续重试
        if ($exception instanceof ConnectException) {
            return true;
        }

        if ($response) {
            // 如果请求有响应，但是状态码大于等于500，继续重试(这里根据自己的业务而定)
            if ($response->getStatusCode() >= 500) {
                return true;
            }
        }

        return false;
    }
}
