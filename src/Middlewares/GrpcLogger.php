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

    public function __construct(LoggerInterface $logger) {
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
        $this->requestLog($method, $argument);
        $call = $continuation($method, $argument, $deserialize, $metadata, $options);

        [$response, $status] = $call->wait();

        $this->responseLog($method, $response ?? null, $status);

        return new ResponseCall($response, $status);
    }


    private function requestLog($name, ?Message $message)
    {
        if ($this->logger) {
            $this->writeLogInfo("[GRPC] Request {$name}", [
                'message' => $this->decodeMessage($message),
            ]);
        }
    }

    private function responseLog($name, ?Message $message, $status)
    {
        if ($this->logger && !is_null($message)) {
            $this->writeLogInfo("[GRPC] Response {$name}", [
                'status_code' => $status->code,
                'status_msg' => $status->details,
                'message' => $this->decodeMessage($message),
            ]);
        }
    }

    private function decodeMessage(?Message $message) : ?array
    {
        if (is_null($message)) {
            return null;
        }

        return json_decode($message->serializeToJsonString(), true);
    }

    protected function writeLogInfo($message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->info($message, $context);
        }
    }
}
