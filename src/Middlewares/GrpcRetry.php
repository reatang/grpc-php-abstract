<?php

namespace Reatang\GrpcPHPAbstract\Middlewares;

use Google\Protobuf\Internal\Message;
use Grpc\Interceptor;
use Grpc\UnaryCall;
use Psr\Log\LoggerInterface;
use Reatang\GrpcPHPAbstract\Call\ResponseCall;
use const Grpc\STATUS_UNAVAILABLE;
use const Grpc\STATUS_ABORTED;

/**
 * GRPC 重试拦截器
 *
 * https://github.com/grpc-ecosystem/go-grpc-middleware/blob/main/retry/retry.go
 */
class GrpcRetry extends Interceptor
{
    /**
     * 最大重试次数
     */
    const MAX_RETRIES = 3;

    /**
     * 每次请求间隔时间:毫秒
     */
    const RETRY_DELAY = 500;

    protected $maxAttempts;
    protected $delay;
    protected $retryableStatusCodes = [];

    /**
     * 日志记录
     *
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        $maxAttempts = self::MAX_RETRIES,
        $delay = self::RETRY_DELAY,
        $retryableStatusCodes = [STATUS_UNAVAILABLE, STATUS_ABORTED]
    ) {
        $this->maxAttempts = $maxAttempts;
        $this->delay = $delay;
        $this->retryableStatusCodes = $retryableStatusCodes;
    }

    public function interceptUnaryUnary(
        $method,
        $argument,
        $deserialize,
        $continuation,
        array $metadata = [],
        array $options = []
    ) {
        if ($this->maxAttempts <= 0) {
            /** @var UnaryCall $call */
            return $continuation($method, $argument, $deserialize, $metadata, $options);
        }

        $status = null;
        for ($attempt = 0; $attempt <= $this->maxAttempts; $attempt++) {
            if ($attempt > 0) {
                $metadata['x-retry-attempt'] = [strval($attempt)];
            }

            /** @var UnaryCall $call */
            $call = $continuation($method, $argument, $deserialize, $metadata, $options);

            [$response, $status] = $call->wait();

            if (!in_array($status->code, $this->retryableStatusCodes)) {
                return new ResponseCall($response, $status);
            }

            if ($this->logger) {
                if ($argument instanceof Message) {
                    $req = json_decode($argument->serializeToJsonString(), true);
                }
                $this->logger->error("[GrpcRetry] {$method} grpc_retry attempt: {$attempt}, got error({$status->code}): {$status->details}", ['argument' => $req ?? $argument]);
            }

            usleep($this->delay * 1000);
        }

        return new ResponseCall(null, $status);
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
