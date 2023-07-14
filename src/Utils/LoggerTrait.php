<?php

namespace Reatang\GrpcPHPAbstract\Utils;

use Psr\Log\LoggerInterface;

trait LoggerTrait
{
    /** @var LoggerInterface $logger */
    protected $logger = null;

    /**
     * @param LoggerInterface $logger
     *
     * @return self
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    protected function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }
}