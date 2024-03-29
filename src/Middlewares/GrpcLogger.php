<?php

namespace Reatang\GrpcPHPAbstract\Middlewares;

use Google\Protobuf\Internal\Message;
use Grpc\Interceptor;
use Psr\Log\LoggerInterface;
use Reatang\GrpcPHPAbstract\Call\ResponseCall;
use Reatang\GrpcPHPAbstract\Utils\LoggerTrait;

/**
 * GRPC 日志记录
 */
class GrpcLogger extends Interceptor
{
    use LoggerTrait;

    /**
     * 日志记录
     *
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    public function interceptUnaryUnary(
        $method,
        $argument,
        $deserialize,
        $continuation,
        array $metadata = [],
        array $options = []
    ) {
        $startTime = microtime(true);

        $call = $continuation($method, $argument, $deserialize, $metadata, $options);
        [$response, $status] = $call->wait();

        $this->responseLog($method, $argument, $response ?? null, $status, $startTime);
        return new ResponseCall($response, $status);
    }

    private function responseLog($name, ?Message $argument, ?Message $response, $status, $startTime)
    {
        $t = (microtime(true) - $startTime) * 1000;

        $this->writeLogInfo("[GRPC] CALL {$name} - Time: {$t}", [
            'status' => "{$status->code}:$status->details",
            'request' => $this->decodeMessage($argument),
            'response' => $this->decodeMessage($response),
        ]);
    }

    private function decodeMessage(?Message $message) : ?array
    {
        if (is_null($message)) {
            return null;
        }

        // 注意，php 的 serializeToJsonString 不支持 google.protobuf.Any 参数的自动解析为JSON，需手动转换
        // 如果 message 参数中存在Any类型，此处会报错
        return json_decode($message->serializeToJsonString(), true);
    }

    protected function writeLogInfo($message, array $context = [])
    {
        $this->logger->info($message, $context);
    }
}
