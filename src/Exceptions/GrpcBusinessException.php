<?php

namespace Reatang\GrpcPHPAbstract\Exceptions;

class GrpcBusinessException extends GrpcException
{
    const BUSINESS_ERROR_PREFIX = '!';
}