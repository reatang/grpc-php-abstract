<?php

namespace Reatang\GrpcPHPAbstract\Tests\MockPHP;

use Grpc\ServerContext;
use OpenTelemetry\API\Baggage\Baggage;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKeys;
use Reatang\GrpcPHPAbstract\Tests\Mock\PB\OTelRequest;
use Reatang\GrpcPHPAbstract\Tests\Mock\PB\OTelResponse;
use Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingRequest;
use Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingResponse;
use Reatang\GrpcPHPAbstract\Tests\Mock\PB\TestServerInterface;
use Reatang\GrpcPHPAbstract\Tests\Mock\TestServerStub;

class MockService extends TestServerStub
{
    public function Ping(PingRequest $request, ServerContext $context): ?PingResponse
    {
        if ($request->getPing() == "" || $request->getPing() == "ping") {
            return new PingResponse(["pong" => "PONG"]);
        } else if ($request->getPing() == "metadata") {
            $metadata = $context->clientMetadata();
            if (!isset($metadata["abc"])) {
                $context->setStatus(\Grpc\Status::status(\Grpc\STATUS_INVALID_ARGUMENT, "参数错误"));
                return null;
            }

            return new PingResponse(["pong" => "PONG" . current($metadata["abc"])]);
        }

        $context->setStatus(\Grpc\Status::status(\Grpc\STATUS_NOT_FOUND, "未找到的测试"));
        return null;
    }

    public function OTel(OTelRequest $request, ServerContext $context): ?OTelResponse
    {
        $m = new GrpcOTelServerMiddleware;

        return $m->handle($request, $context, function (OTelRequest $request, ServerContext $context): ?OTelResponse {
            /** @var Baggage $baggage */
            $baggage = Context::getCurrent()->get(ContextKeys::baggage());
            /** @var Span $span */
            $span = Context::getCurrent()->get(ContextKeys::span());

            return new OTelResponse([
                "trace" => $span->getContext()->getTraceId(),
                "baggage" => $baggage->getValue("baggage1")
            ]);
        });
    }
}