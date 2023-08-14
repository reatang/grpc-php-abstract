<?php

namespace Reatang\GrpcPHPAbstract\Utils;

use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use ArrayAccess;
use function get_class;
use function gettype;
use InvalidArgumentException;
use function is_array;
use function is_object;
use function sprintf;
use function strcasecmp;
use Traversable;

final class MetadataAccessGetterSetter implements PropagationGetterInterface, PropagationSetterInterface
{
    private static ?self $instance = null;

    /**
     * 单例
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /** {@inheritdoc} */
    public function keys($carrier): array
    {
        if ($this->isSupportedCarrier($carrier)) {
            $keys = [];
            foreach ($carrier as $key => $_) {
                $keys[] = (string)$key;
            }

            return $keys;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unsupported carrier type: %s.',
                is_object($carrier) ? get_class($carrier) : gettype($carrier),
            )
        );
    }

    /** {@inheritdoc} */
    public function get($carrier, string $key): ?string
    {
        if ($this->isSupportedCarrier($carrier)) {
            $value = $carrier[$this->resolveKey($carrier, $key)] ?? null;
            if (is_array($value) && $value) {
                return current($value);
            }

            return $value;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unsupported carrier type: %s. Unable to get value associated with key:%s',
                is_object($carrier) ? get_class($carrier) : gettype($carrier),
                $key
            )
        );
    }

    /** {@inheritdoc} */
    public function set(&$carrier, string $key, string $value): void
    {
        if ($key === '') {
            throw new InvalidArgumentException('Unable to set value with an empty key');
        }
        if ($this->isSupportedCarrier($carrier)) {
            if (($r = $this->resolveKey($carrier, $key)) !== $key) {
                unset($carrier[$r]);
            }

            $carrier[$key] = [$value];

            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unsupported carrier type: %s. Unable to set value associated with key:%s',
                is_object($carrier) ? get_class($carrier) : gettype($carrier),
                $key
            )
        );
    }

    private function isSupportedCarrier($carrier): bool
    {
        return is_array($carrier) || $carrier instanceof ArrayAccess && $carrier instanceof Traversable;
    }

    private function resolveKey($carrier, string $key): string
    {
        if (isset($carrier[$key])) {
            return $key;
        }

        foreach ($carrier as $k => $_) {
            $k = (string)$k;
            if (strcasecmp($k, $key) === 0) {
                return $k;
            }
        }

        return $key;
    }
}
