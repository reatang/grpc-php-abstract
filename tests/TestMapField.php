<?php
namespace Reatang\GrpcPHPAbstract\Tests;

use Google\Protobuf\Internal\MapField;
use Reatang\GrpcPHPAbstract\Tests\Mock\PB\SomeMap;
use Reatang\GrpcPHPAbstract\Tests\Mock\TestServerAbsRpc;
use Reatang\GrpcPHPAbstract\Tests\Mock\TestServerGatewayRpc;

class TestMapField extends \PHPUnit\Framework\TestCase
{
    public function testMapField() {
        $map = new SomeMap;
        $map->setMap1(['a' => '1', 'b' => '2']);

        $data = $map->serializeToString();
        file_put_contents(__DIR__ ."/data/some_map.bin", $data);
    }

    public function testReadMapField() {
        $map = new SomeMap;
        $data = file_get_contents(__DIR__ ."/data/some_map.bin");

        $map->mergeFromString($data);

        foreach ($map->getMap1() as $key => $value) {
            echo $key . ": " . $value . "\n";
        }
    }
}