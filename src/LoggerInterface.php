<?php

declare(strict_types=1);

namespace Laminas\Log;

use Traversable;

interface LoggerInterface
{
    /**
     * @param string $message
     * @param array|Traversable $extra
     */
    public function emerg($message, $extra = []): LoggerInterface;

    /**
     * @param string $message
     * @param array|Traversable $extra
     */
    public function alert($message, $extra = []): LoggerInterface;

    /**
     * @param string $message
     * @param array|Traversable $extra
     */
    public function crit($message, $extra = []): LoggerInterface;

    /**
     * @param string $message
     * @param array|Traversable $extra
     */
    public function err($message, $extra = []): LoggerInterface;

    /**
     * @param string $message
     * @param array|Traversable $extra
     */
    public function warn($message, $extra = []): LoggerInterface;

    /**
     * @param string $message
     * @param array|Traversable $extra
     */
    public function notice($message, $extra = []): LoggerInterface;

    /**
     * @param string $message
     * @param array|Traversable $extra
     */
    public function info($message, $extra = []): LoggerInterface;

    /**
     * @param string $message
     * @param array|Traversable $extra
     */
    public function debug($message, $extra = []): LoggerInterface;

    /**
     * @param int $priority
     * @param string $message
     * @param array|Traversable $extra
     */
    public function log($priority, $message, $extra = []): LoggerInterface;
}
