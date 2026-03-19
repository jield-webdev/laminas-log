<?php

declare(strict_types=1);

namespace Laminas\Log\Writer\FirePhp;

interface FirePhpInterface
{
    /**
     * Determine whether or not FirePHP is enabled
     */
    public function getEnabled(): bool;

    /**
     * Log an error message
     *
     * @param string $line
     * @param string|null $label
     */
    public function error($line, $label = null);

    /**
     * Log a warning
     *
     * @param string $line
     * @param string|null $label
     */
    public function warn($line, $label = null);

    /**
     * Log informational message
     *
     * @param string $line
     * @param string|null $label
     */
    public function info($line, $label = null);

    /**
     * Log a trace
     *
     * @param string $line
     */
    public function trace($line);

    /**
     * Log a message
     *
     * @param string $line
     * @param string|null $label
     */
    public function log($line, $label = null);
}
