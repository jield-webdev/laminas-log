<?php

declare(strict_types=1);

namespace Laminas\Log\Writer;

class Mock extends AbstractWriter
{
    /**
     * array of log events
     *
     * @var array
     */
    public array $events = [];

    /**
     * shutdown called?
     *
     * @var bool
     */
    public bool $shutdown = false;

    /**
     * Record shutdown
     *
     * @return void
     */
    public function shutdown(): void
    {
        $this->shutdown = true;
    }

    /**
     * Write a message to the log.
     *
     * @param array $event event data
     * @return void
     */
    protected function doWrite(array $event): void
    {
        $this->events[] = $event;
    }
}
