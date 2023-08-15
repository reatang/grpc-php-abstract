<?php

namespace Reatang\GrpcPHPAbstract\Tests\Services;

use Google\Protobuf\Internal\Message;
use Grpc\ServerContext;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;
use Reatang\GrpcPHPAbstract\Utils\MetadataAccessGetterSetter;

class GrpcOTelServerMiddleware
{
    public function handle(Message $message, ServerContext $context, $next) : ?Message
    {
        $parentContext = Globals::propagator()->extract($context->clientMetadata(), MetadataAccessGetterSetter::getInstance());

        $span = Globals::tracerProvider()
                       ->getTracer("reatang/grpc-otel-server-middleware")
                       ->spanBuilder("GRPC " . $context->method())
                       ->setSpanKind(SpanKind::KIND_SERVER)
                       ->setParent($parentContext)
                       ->startSpan();
        $scope = Context::storage()->attach($span->storeInContext($parentContext));

        $response = $next($message, $context);

        $scope->detach();
        if (!empty($context->status()) && $context->status()['code'] != \Grpc\STATUS_OK) {
            $span->setStatus(StatusCode::STATUS_ERROR, $context->status()['details'])->end();
        } else {
            $span->setStatus(StatusCode::STATUS_OK)->end();
        }

        return $response;
    }
}