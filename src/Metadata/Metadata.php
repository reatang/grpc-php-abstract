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

    public static function pairs(string ...$kv): MD {
        if (count($kv) % 2 == 1) {
            throw new \Exception(sprintf("metadata: Pairs got the odd number of input pairs for metadata: %d", count($kv)));
        }
        $md = new MD;
        for ($i = 0; $i < count($kv); $i += 2) {
            $md->append($kv[$i], $kv[$i+1]);
        }
        return $md;
    }
}