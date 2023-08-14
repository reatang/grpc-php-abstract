<?php

namespace Reatang\GrpcPHPAbstract\Tests\Services;

use Grpc\ServerContext;
use OpenTelemetry\API\Baggage\Baggage;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\ContextKeys;
use Reatang\GrpcPHPAbstract\Tests\Mock\PB\OtelRequest;
use Reatang\GrpcPHPAbstract\Tests\Mock\PB\OtelResponse;
use Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingRequest;
use Reatang\GrpcPHPAbstract\Tests\Mock\PB\PingResponse;
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

    public function Otel(OtelRequest $request, ServerContext $context): ?OtelResponse
    {
        $m = new GrpcOtelServerMiddleware;

        return $m->handle($request, $context, function (OtelRequest $request, ServerContext $context): ?OtelResponse {
            $context = Globals::propagator()->extract($context->clientMetadata());

            /** @var Baggage $baggage */
            $baggage = $context->get(ContextKeys::baggage());
            /** @var Span $span */
            $span = $context->get(ContextKeys::span());

            return new OtelResponse([
                "trace" => $span->getContext()->getTraceId(),
                "baggage" => $baggage->getValue("baggage1")
            ]);
        });
    }
}