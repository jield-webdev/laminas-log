<?php

declare(strict_types=1);

namespace Laminas\Log;

trait LoggerAwareTrait
{
    /** @var LoggerInterface */
    protected LoggerInterface $logger;

    /**
     * Get logger object
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Set logger object
     */
    public function setLogger(LoggerInterface $logger): mixed
    {
        $this->logger = $logger;

        return $this;
    }
}
