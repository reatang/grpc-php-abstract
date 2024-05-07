<?php
namespace Reatang\GrpcPHPAbstract\Tests;

use Reatang\GrpcPHPAbstract\Tests\Mock\TestServerAbsRpc;
use Reatang\GrpcPHPAbstract\Tests\Mock\TestServerGatewayRpc;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function getMockClient(): TestServerAbsRpc
    {
        return new TestServerAbsRpc("127.0.0.1:9099");
    }

    protected function getMockGatewayClient(): TestServerGatewayRpc
    {
        return new TestServerGatewayRpc("127.0.0.1:9098");
    }
}