<?php

namespace Reatang\GrpcPHPAbstract\Utils;

use Psr\Log\LoggerInterface;

trait LoggerTrait
{
    /** @var LoggerInterface $logger */
    protected $logger = null;

    /**
     * @param LoggerInterface|null $logger
     *
     * @return $this
     */
    public function setLogger(?LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return LoggerInterface|null
     */
    protected function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }
}