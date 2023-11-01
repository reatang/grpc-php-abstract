<?php

namespace Reatang\GrpcPHPAbstract\Metadata;

/**
 * grpc上下文元数据
 */
class Metadata
{
    /** @var MD */
    public MD $header;

    /** @var MD */
    public MD $trailer;

    public function __construct(array $header = [], array $trailer = [])
    {
        $this->header = new MD($header);
        $this->trailer = new MD($trailer);
    }

    public static function create(array $header = [], array $trailers = []): Metadata
    {
        return new static($header, $trailers);
    }

    public static function pairs(string ...$kv): MD
    {
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