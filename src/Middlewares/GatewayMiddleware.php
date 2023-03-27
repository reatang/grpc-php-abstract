<?php

namespace Reatang\GrpcPHPAbstract;

use Psr\Http\Message\RequestInterface;
use Reatang\GrpcPHPAbstract\Metadata\GatewayHandle;
use Reatang\GrpcPHPAbstract\Metadata\Metadata;

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
        return static function (callable $handler) use ($timeout): callable {
            return static function (RequestInterface $request, array $options) use ($handler, $timeout) {
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
     * @param array $metadata
     * @param array $trailers
     *
     * @return callable
     */
    public static function GrpcMetadata(array $metadata, array $trailers = []): callable
    {
        return static function (callable $handler) use ($metadata, $trailers) : callable  {
            return static function (RequestInterface $request, array $options) use ($metadata, $trailers, $handler) {
                $md = Metadata::create($metadata, $trailers);

                foreach (GatewayHandle::toHeaders($md) as $k => $v) {
                    $request = $request->withAddedHeader($k, $v);
                }

                return $handler($request, $options);
            };
        };
    }
}