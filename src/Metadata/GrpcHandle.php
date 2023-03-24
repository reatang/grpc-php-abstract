<?php

namespace Reatang\GrpcPHPAbstract\Metadata;

use Grpc\AbstractCall;

class GrpcHandle
{
    /**
     * 解码 grpc 的应答结果
     *
     * @param AbstractCall $call
     *
     * @return Metadata
     */
    public static function parseCall(AbstractCall $call): Metadata {
        return new Metadata(self::toLowKey($call->getMetadata()), self::toLowKey($call->getTrailingMetadata()));
    }

    protected static function toLowKey(array $arr): array
    {
        $a = [];
        foreach ($arr as $k => $v) {
            $a[strtolower($k)] = $v;
        }

        return $a;
    }
}