<?php

declare(strict_types=1);

namespace Laminas\Log;

/**
 * Logger aware interface
 */
interface LoggerAwareInterface
{
    /**
     * Set logger instance
     */
    public function setLogger(LoggerInterface $logger): void;

    /**
     * Get logger instance. Currently commented out as this would possibly break
     * existing implementations.
     *
     * @return null|LoggerInterface
     */
    // public function getLogger();
}
