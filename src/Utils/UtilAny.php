<?php

namespace Reatang\GrpcPHPAbstract\Utils;

use Google\Protobuf\Any;
use Google\Protobuf\Internal\Message;

/**
 * 解析any参数
 */
class UtilAny
{
    protected static array $anyClassRegister = [];

    /**
     * @param string $protoType 一个proto类型，<package>.<Message>, 如：reatang.crpc.SimpleDetail
     * @param string $targetClass 对应的目标类名
     *
     * @return void
     */
    public static function register(string $protoType, string $targetClass)
    {
        if (empty($protoType) || empty($targetClass)) {
            return;
        }

        self::$anyClassRegister["type.googleapis.com/{$protoType}"] = $targetClass;
    }

    /**
     * 按照注册的类解析
     *
     * @param string $bin protobuf 二进制数据
     *
     * @return Message|null
     * @throws \Exception
     */
    public static function decodeAny(Any $any): ?Message
    {
        if (empty($any->getTypeUrl()) || !isset(self::$anyClassRegister[$any->getTypeUrl()])) {
            return null;
        }

        $targetClass = self::$anyClassRegister[$any->getTypeUrl()];

        /** @var Message $target */
        $target = new $targetClass;
        $target->mergeFromString($any->getValue());

        return $target;
    }
}