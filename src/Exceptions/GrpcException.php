<?php

namespace Reatang\GrpcPHPAbstract\Exceptions;

class GrpcException extends \Exception
{
    protected array $details = [];

    public function __construct(string $message, int $code, array $details = [])
    {
        $this->details = $details;

        parent::__construct($message, $code);
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}