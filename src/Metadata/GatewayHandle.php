<?php

namespace Reatang\GrpcPHPAbstract\Metadata;

class GatewayHandle
{
    // MetadataHeaderPrefix is the http prefix that represents custom metadata
    // parameters to or from a gRPC call.
    const MetadataHeaderPrefix = "Grpc-Metadata-";
    protected const MetadataHeaderPrefixLen = 14;

    // MetadataPrefix is prepended to permanent HTTP header keys (as specified
    // by the IANA) when added to the gRPC context.
    const MetadataPrefix = "grpcgateway-";

    // MetadataTrailerPrefix is prepended to gRPC metadata as it is converted to
    // HTTP headers in a response handled by grpc-gateway
    const MetadataTrailerPrefix = "Grpc-Trailer-";
    protected const MetadataTrailerPrefixLen = 13;

    const metadataGrpcTimeout = "Grpc-Timeout";
    const metadataHeaderBinarySuffix = "-Bin";

    /**
     * 解码 grpc-gateway 的应答头
     *
     * @param $headers[][]
     *
     * @return void
     */
    public static function parseHeader(array $headers): Metadata
    {
        $m = $t = [];
        foreach ($headers as $h => $values) {
            if (strpos($h, self::MetadataHeaderPrefix) !== false) {
                $hName = substr($h, self::MetadataHeaderPrefixLen);

                $m[strtolower($hName)] = $values;
            } elseif (strpos($h, self::MetadataTrailerPrefix) !== false) {
                $hName = substr($h, self::MetadataTrailerPrefixLen);

                $t[strtolower($hName)] = $values;
            }
        }

        return new Metadata($m, $t);
    }

    /**
     * 编码 grpc-gateway 的应答头
     * @param Metadata $metadata
     *
     * @return array
     */
    public static function toHeader(Metadata $metadata): array
    {
        $n = [];
        foreach ($metadata->header->toArray() as $h => $v) {
            $n[GatewayHandle::MetadataHeaderPrefix . $h] = is_array($v) ? join(';', $v) : $v;
        }
        foreach ($metadata->trailer->toArray() as $h => $v) {
            $n[GatewayHandle::MetadataTrailerPrefix . $h] = is_array($v) ? join(';', $v) : $v;
        }

        return $n;
    }
}