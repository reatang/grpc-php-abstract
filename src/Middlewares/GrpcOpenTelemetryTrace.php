<?php

namespace Reatang\GrpcPHPAbstract\Middlewares;

use Grpc\Interceptor;
use Grpc\UnaryCall;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use OpenTelemetry\SemConv\TraceAttributes;
use Reatang\GrpcPHPAbstract\Call\ResponseCall;
use Reatang\GrpcPHPAbstract\Utils\MetadataAccessGetterSetter;

/**
 * PHP GRPC 支持 OpenTelemetryTrace
 */
class GrpcOpenTelemetryTrace extends Interceptor
{
    /**
     * @var TracerProviderInterface
     */
    protected $tracer;

    public function __construct()
    {
        $this->tracer = Globals::tracerProvider()->getTracer("reatang/grpc-php-abstract");
    }

    public function interceptUnaryUnary(
        $method,
        $argument,
        $deserialize,
        $continuation,
        array $metadata = [],
        array $options = []
    ) {
        $name = sprintf("CALL %s", $method);
        [$_service, $_method] = explode('/', ltrim($method, '/'), 2);
        $attributes = [
            TraceAttributes::RPC_SERVICE => $_service,
            TraceAttributes::RPC_METHOD => $_method,
        ];

        $span = $this->tracer->spanBuilder($name)
                             ->setSpanKind(SpanKind::KIND_CLIENT)
                             ->setAttributes($attributes)
                             ->setParent(Context::getCurrent())
                             ->startSpan();
        $ctx = $span->storeInContext(Context::getCurrent());
        Globals::propagator()->inject($metadata, MetadataAccessGetterSetter::getInstance(), $ctx);

        /** @var UnaryCall $call */
        $call = $continuation($method, $argument, $deserialize, $metadata, $options);

        [$response, $status] = $call->wait();

        $span->setAttribute(TraceAttributes::RPC_GRPC_STATUS_CODE, $status->code);

        if ($status->code != \Grpc\STATUS_OK) {
            $span->setAttributes([
                TraceAttributes::RPC_GRPC_STATUS_CODE => $status->code,
            ]);
            $span->setStatus(StatusCode::STATUS_ERROR, $status->details)->end();
        } else {
            $span->setStatus(StatusCode::STATUS_OK)->end();
        }

        return new ResponseCall($response, $status);
    }
}