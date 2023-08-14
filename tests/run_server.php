<?php

use OpenTelemetry\API\Baggage\Propagation\BaggagePropagator;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Propagation\MultiTextMapPropagator;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Reatang\GrpcPHPAbstract\Tests\Services\MockService;

include "../vendor/autoload.php";

// 启动链路追踪SDK
$tracerProvider = new TracerProvider(
    new SimpleSpanProcessor(
        (new ConsoleSpanExporterFactory())->create()
    ),
    new ParentBased(new AlwaysOnSampler()),
);

Sdk::builder()
   ->setTracerProvider($tracerProvider)
   ->setPropagator(new MultiTextMapPropagator([
       TraceContextPropagator::getInstance(),
       BaggagePropagator::getInstance(),
   ]))
   ->setAutoShutdown(true)
   ->buildAndRegisterGlobal();


// 启动server
$server = new \Grpc\RpcServer([]);
$server->addHttp2Port("127.0.0.1:9099");
$server->handle(new MockService());

$server->run();