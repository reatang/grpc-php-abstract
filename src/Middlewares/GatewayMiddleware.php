<?php

namespace Reatang\GrpcPHPAbstract\Middlewares;

use GuzzleHttp\Psr7\Response;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Reatang\GrpcPHPAbstract\Metadata\GatewayHandle;
use Reatang\GrpcPHPAbstract\Metadata\Metadata;
use Reatang\GrpcPHPAbstract\Utils\MetadataAccessGetterSetter;

class GatewayMiddleware
{
    /**
     * 设置超时时间
     *
     * H 小时
     * M 分钟
     * S 秒
     * m 毫秒
     * u 微秒
     * n 纳秒
     *
     * @param $timeout
     *
     * @return callable
     */
    public static function GrpcGatewayOpt($timeout): callable
    {
        return function (callable $handler) use ($timeout): callable {
            return function (RequestInterface $request, array $options) use ($handler, $timeout) {
                $request = $request
                    ->withAddedHeader('Accept', '*')
                    ->withAddedHeader(GatewayHandle::metadataGrpcTimeout, $timeout);

                return $handler($request, $options);
            };
        };
    }

    /**
     * 预输入全局处理 metadata
     *
     * @param array $headers
     * @param array $trailers
     *
     * @return callable
     */
    public static function GrpcMetadata(array $headers, array $trailers = []): callable
    {
        return function (callable $handler) use ($headers, $trailers) : callable  {
            return function (RequestInterface $request, array $options) use ($headers, $trailers, $handler) {
                $md = Metadata::create($headers, $trailers);

                foreach (GatewayHandle::toHeader($md) as $k => $v) {
                    $request = $request->withAddedHeader($k, $v);
                }

                return $handler($request, $options);
            };
        };
    }

    /**
     * 重试
     *
     * @param int $maxAttempt
     * @param int $delay
     *
     * @return \Closure
     */
    public static function retry(int $maxAttempt = 3, int $delay = 300): callable
    {
        return function (callable $handler) use ($maxAttempt, $delay) {
            return new GatewayRetry($maxAttempt, $delay, $handler);
        };
    }

    public static function openTelemetryTrace(): callable
    {
        $tracer = Globals::tracerProvider()->getTracer("reatang/grpc-php-abstract", "0.5.0");

        return function (callable $handler) use ($tracer) {
            return function (RequestInterface $request, array $options) use ($handler, $tracer) {
                $name = sprintf("POST %s", $request->getUri()->getPath());

                $span = $tracer->spanBuilder($name)
                               ->setSpanKind(SpanKind::KIND_CLIENT)
                               ->setParent(Context::getCurrent())
                               ->startSpan();
                $ctx = $span->storeInContext(Context::getCurrent());
                $scope = $span->activate();

                $metadata = [];
                Globals::propagator()->inject($metadata, MetadataAccessGetterSetter::getInstance(), $ctx);

                foreach (GatewayHandle::toHeader(Metadata::create($metadata)) as $k => $v) {
                    $request = $request->withAddedHeader($k, $v);
                }

                /** @var \GuzzleHttp\Promise\FulfilledPromise $promise */
                $promise = $handler($request, $options);

                $promise->then(function (ResponseInterface $response) use ($span, $scope) {
                    $span->setAttributes([
                        TraceAttributes::HTTP_STATUS_CODE => $response->getStatusCode(),
                    ]);
                    $span->setStatus(StatusCode::STATUS_OK)->end();
                    $scope->detach();
                }, function (\Throwable $e) use ($span, $scope) {
                    $span->setAttributes([
                        TraceAttributes::EXCEPTION_TYPE => get_class($e),
                        TraceAttributes::EXCEPTION_MESSAGE => $e->getMessage(),
                    ]);
                    $span->setStatus(StatusCode::STATUS_ERROR, $e->getMessage())->end();
                    $scope->detach();
                });

                return $promise;
            };
        };
    }
}