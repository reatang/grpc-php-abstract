<?php

namespace Reatang\GrpcPHPAbstract\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Reatang\GrpcPHPAbstract\Exceptions\GrpcException;
use Reatang\GrpcPHPAbstract\Metadata\GatewayHandle;
use Reatang\GrpcPHPAbstract\Metadata\Metadata;
use Reatang\GrpcPHPAbstract\Middlewares\GatewayMiddleware;
use Reatang\GrpcPHPAbstract\Utils\ExceptionFunc;

abstract class GatewayBaseClient
{
    protected string $host;

    protected Client $client;

    /**
     * 函数的应答对照（必写）
     *
     * @var GatewayRoute[]
     */
    protected array $methodResponseMap = [];

    /**
     * @param string     $host
     * @param callable[] $middleware
     */
    public function __construct(string $host, array $middleware = [])
    {
        $this->host = $host;

        $h = HandlerStack::create();
        foreach ($middleware as $name => $call) {
            $h->push($call, $name);
        }
        $h->push(Middleware::httpErrors(), 'http_errors');
        $h->push(Middleware::prepareBody(), 'prepare_body');
        $h->push(GatewayMiddleware::retry(), 'retry');

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

    /**
     * @param string $methodName
     * @param string $url gateway url
     * @param string $response
     *
     * @return void
     * @throws GrpcException
     */
    public function addRoute(string $methodName, string $url, string $response)
    {
        if (!isset($this->methodResponseMap[$methodName])) {
            $this->methodResponseMap[$methodName] = new GatewayRoute($url, $response);
        } else {
            throw new GrpcException(__CLASS__ . " {$methodName} is registered", 500);
        }
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     * @throws GrpcException|\GuzzleHttp\Exception\GuzzleException
     */
    public function __call($name, $arguments)
    {
        if (!isset($this->methodResponseMap[$name])) {
            throw new GrpcException(__CLASS__ . " {$name} not config route", 500);
        }

        $route = $this->methodResponseMap[$name];

        try {
            $opts = [];
            if (isset($arguments[0])) {
                $opts[RequestOptions::BODY] = $arguments[0]->serializeToJsonString();
            }

            $response = $this->client->request('POST', $route->getUrl(), $opts);
        } catch (ServerException $exception) {
            throw $this->exception($exception);
        }

        $respPb = $route->newResponse();
        $respPb->mergeFromJsonString($response->getBody(), true);

        return $respPb;
    }
}
