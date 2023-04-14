<?php
namespace Reatang\GrpcPHPAbstract\Middlewares;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;

/**
 * http 重试中间件
 * 使用方法：
 * $handler->push(Middleware::retry(GatewayRetry::retryDecider(), GatewayRetry::retryDelay()), 'retry');
 */
class GatewayRetry
{
    public static $MAX_RETRIES = 5; // 次数
    public static $RETRY_DELAY = 500; // ms

    /**
     * 检测是否需要重试
     * 
     * @return callable
     */
    public static function retryDecider()
    {
        return function ($retries, Request $request, Response $response = null, \Exception $exception = null)
        {
            if ($retries >= self::$MAX_RETRIES) {
                return false;
            }

            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($response) {
                if ($response->getStatusCode() >= 500) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * 每次重试加100ms
     *
     * @return callable
     */
    public static function retryDelay()
    {
        return function ($numberOfRetries) {
            return self::$RETRY_DELAY + $numberOfRetries * 100;
        };
    }
}