<?php

namespace Reatang\GrpcPHPAbstract\Call;

class ResponseCall
{
    protected $response;

    protected $status;

    public function __construct($response, $status)
    {
        $this->response = $response;
        $this->status = $status;
    }

    public function wait()
    {
        return [$this->response, $this->status];
    }
}