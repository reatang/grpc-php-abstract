<?php

namespace Reatang\GrpcPHPAbstract\Metadata;

/**
 * grpc上下文元数据
 */
class Metadata
{
    /** @var MD */
    public MD $metadata;

    /** @var MD */
    public MD $trailers;

    public function __construct(array $metadata = [], array $trailers = [])
    {
        $this->metadata = new MD($metadata);
        $this->trailers = new MD($trailers);
    }

    public static function create(array $metadata = [], array $trailers = []): Metadata
    {
        return new static($metadata, $trailers);
    }

    public function toHeaders(): array
    {
        $n = [];
        foreach ($this->metadata->toArray() as $h => $v) {
            $n[GatewayHandle::MetadataHeaderPrefix . $h] = is_array($v) ? join(';', $v) : $v;
        }
        foreach ($this->trailers->toArray() as $h => $v) {
            $n[GatewayHandle::MetadataTrailerPrefix . $h] = is_array($v) ? join(';', $v) : $v;
        }

        return $n;
    }
}