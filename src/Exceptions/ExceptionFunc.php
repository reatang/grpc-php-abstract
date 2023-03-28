<?php

namespace Reatang\GrpcPHPAbstract\Exceptions;

use GuzzleHttp\Exception\ServerException;
use const Grpc\STATUS_UNKNOWN;

/**
 * GrpcBusinessException 格式：{"code":2, "message":"!20202:这是一条错误", "details":[]}
 * GrpcException 格式：{"code":2, "message":"Some error", "details":[]}
 */
class ExceptionFunc extends \Exception
{
    /**
     * 网关的报错
     *
     * @param ServerException $exception
     *
     * @return \Exception
     */
    public static function gatewayException(ServerException $exception): GrpcException
    {
        $errJson = json_decode($exception->getResponse()->getBody()->getContents(), true);

        if (substr($errJson['message'], 0, 1) == GrpcBusinessException::BUSINESS_ERROR_PREFIX) {
            $errInfo = explode(':', $errJson['message']);
            if (count($errInfo) == 2 && ($errCode = intval($errInfo[0])) == STATUS_UNKNOWN) {
                return new GrpcBusinessException($errInfo[1], $errCode, $errJson['details']);
            }
        }

        return new GrpcException($errJson["message"], $errJson["code"], $errJson["details"]);
    }

    /**
     * Grpc 客户端的报错
     *
     * @param object $status
     *
     * @return GrpcException
     */
    public static function grpcException(object $status): GrpcException
    {
        if (substr($status->details, 0, 1) == GrpcBusinessException::BUSINESS_ERROR_PREFIX) {
            $errInfo = explode(':', $status->details);
            if (count($errInfo) == 2 && ($errCode = intval($errInfo[0])) == STATUS_UNKNOWN) {
                return new GrpcBusinessException($errInfo[1], $errCode);
            }
        }

        return new GrpcException($status->details, $status->code);
    }
}