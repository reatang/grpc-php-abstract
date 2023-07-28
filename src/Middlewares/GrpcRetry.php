<?php

namespace Reatang\GrpcPHPAbstract\Middlewares;

use Google\Protobuf\Internal\Message;
use Grpc\Interceptor;
use Grpc\UnaryCall;
use Psr\Log\LoggerInterface;
use Reatang\GrpcPHPAbstract\Call\ResponseCall;
use Reatang\GrpcPHPAbstract\Utils\LoggerTrait;
use const Grpc\STATUS_UNAVAILABLE;
use const Grpc\STATUS_ABORTED;

/**
 * GRPC 重试拦截器
 *
 * https://github.com/grpc-ecosystem/go-grpc-middleware/blob/main/retry/retry.go
 */
class GrpcRetry extends Interceptor
{
    use LoggerTrait;

    /** @var int 最大重试次数 */
    protected $maxAttempts;

    /** @var int 重试间隔时间，单位：毫秒ms */
    protected $delay;

    protected $retryableStatusCodes = [];

    public function __construct(
        $maxAttempts = 3,
        $delay = 300,
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

            if (!in_array($status->code, $this->retryableStatusCodes) ||
                ($status->code == \Grpc\STATUS_UNAVAILABLE && strpos($status->details, 'failed to connect') !== false)
            ) {
                return new ResponseCall($response, $status);
            }

            if ($this->logger) {
                if ($argument instanceof Message) {
                    // 如果 message 参数中存在Any类型，此处会报错
                    $req = json_decode($argument->serializeToJsonString(), true);
                }
                $this->logger->error("[GrpcRetry] {$method} grpc_retry attempt: {$attempt}, got error({$status->code}): {$status->details}", ['argument' => $req ?? $argument]);
            }

            usleep($this->delay * 1000);
        }

        return new ResponseCall(null, $status);
    }
}
