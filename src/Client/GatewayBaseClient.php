<?php

namespace Reatang\GrpcPHPAbstract\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;
use Reatang\GrpcPHPAbstract\Exceptions\ExceptionFunc;
use Reatang\GrpcPHPAbstract\Exceptions\GrpcException;
use Reatang\GrpcPHPAbstract\Metadata\GatewayHandle;
use Reatang\GrpcPHPAbstract\Metadata\Metadata;

abstract class GatewayBaseClient
{
    protected string $host;

    protected Client $client;

    /**
     * @param string     $host
     * @param callable[] $middleware
     */
    public function __construct(string $host, array $middleware)
    {
        $this->host = $host;

        $h = HandlerStack::create();
        foreach ($middleware as $name => $call) {
            $h->push($call, $name);
        }
        $h->push(Middleware::httpErrors(), 'http_errors');
        $h->push(Middleware::prepareBody(), 'prepare_body');

        $this->client = new Client([
            'base_uri' => $this->host(),
            'handler'  => $h,
        ]);
    }

    public function host(): string { return $this->host; }

    /**
     * 错误统一化
     *
     * @param ServerException $exception
     *
     * @return GrpcException
     */
    protected function exception(ServerException $exception): GrpcException
    {
        return ExceptionFunc::gatewayException($exception);
    }

    /**
     * metadata 获取统一化
     *
     * @param ResponseInterface $response
     *
     * @return Metadata
     */
    protected function metadata(ResponseInterface $response): Metadata
    {
        return GatewayHandle::parseHeader($response->getHeaders());
    }
}