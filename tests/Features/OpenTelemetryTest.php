<?php

namespace Reatang\GrpcPHPAbstract\Tests\Features;

use OpenTelemetry\API\Baggage\Baggage;
use OpenTelemetry\API\Baggage\Propagation\BaggagePropagator;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Propagation\MultiTextMapPropagator;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Reatang\GrpcPHPAbstract\Middlewares\GrpcOpenTelemetryTrace;
use Reatang\GrpcPHPAbstract\Tests\Mock\PB\OTelRequest;
use Reatang\GrpcPHPAbstract\Tests\Mock\TestServerAbsRpc;
use Reatang\GrpcPHPAbstract\Tests\TestCase;

class OpenTelemetryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

//        $processor = new SimpleSpanProcessor((new ConsoleSpanExporterFactory())->create());
        $processor = new NoopSpanProcessor();
        $tracerProvider = new TracerProvider(
            $processor,
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
    }

    protected function getMockClient(): TestServerAbsRpc
    {
        return new TestServerAbsRpc("127.0.0.1:9099", [
            new GrpcOpenTelemetryTrace(),
        ]);
    }

    public function testBase()
    {
        $response = $this->getMockClient()->OTel(new OTelRequest());

        $this->assertNotEquals($response->getTrace(), '00000000000000000000000000000000');
        $this->assertTrue(!empty($response->getTrace()));
    }

    public function testBaggage()
    {
        $baggageVar = "123";
        $scope = Baggage::getCurrent()->toBuilder()->set("baggage1", $baggageVar)->build()->activate();

        $response = $this->getMockClient()->OTel(new OTelRequest());

        $scope->detach();

        $this->assertTrue(!empty($response->getTrace()));
        $this->assertNotEquals($response->getTrace(), '00000000000000000000000000000000');
        $this->assertEquals($baggageVar, $response->getBaggage());
    }
}