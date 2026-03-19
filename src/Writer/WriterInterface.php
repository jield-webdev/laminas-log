<?php

declare(strict_types=1);

namespace Laminas\Log\Writer;

use Laminas\Log\Filter\FilterInterface as Filter;
use Laminas\Log\Formatter\FormatterInterface as Formatter;

interface WriterInterface
{
    /**
     * Add a log filter to the writer
     *
     * @param int|string|Filter $filter
     */
    public function addFilter($filter): WriterInterface;

    /**
     * Set a message formatter for the writer
     *
     * @param string|Formatter $formatter
     */
    public function setFormatter($formatter): WriterInterface;

    /**
     * Write a log message
     *
     * @param array $event
     */
    public function write(array $event): WriterInterface;

    /**
     * Perform shutdown activities
     */
    public function shutdown(): void;
}
