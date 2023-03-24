<?php

namespace Reatang\GrpcPHPAbstract\Metadata;

class MD implements \Countable
{
    /** @var array[] */
    protected $md = [];

    public function __construct($md = [])
    {
        $this->md = $md;
    }

    public function len(): int
    {
        return count($this->md);
    }

    public function set($key, ...$val)
    {
        if (count($val) == 0) {
            return;
        }

        $this->md[strtolower($key)] = $val;
    }

    public function get($key): array
    {
        return $this->md[strtolower($key)] ?? [];
    }

    public function getOne($key): string
    {
        $v = $this->get($key);

        return empty($v) ? "" : current($v);
    }

    public function append($key, $val)
    {
        $key = strtolower($key);

        if (empty($this->md[$key])) {
            $this->md[$key] = [];
        }

        if (!is_array($this->md[$key])) {
            $this->md[$key] = [$this->md[$key]];
        }

        $this->md[$key][] = $val;
    }

    public function delete($key)
    {
        $key = strtolower($key);

        unset($this->md[$key]);
    }

    public function count(): int
    {
        return $this->len();
    }

    public function toArray(): array
    {
        return $this->md;
    }
}