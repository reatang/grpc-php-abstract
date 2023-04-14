<?php

namespace Reatang\GrpcPHPAbstract\Client;

class GatewayRoute
{
    protected string $url;
    protected string $response;

    public function __construct(string $url, string $response)
    {
        $this->url = $url;
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getResponse(): string
    {
        return $this->response;
    }

    public function newResponse()
    {
        return new $this->response;
    }

}