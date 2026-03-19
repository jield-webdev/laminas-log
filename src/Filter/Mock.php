<?php

declare(strict_types=1);

namespace Laminas\Log\Filter;

class Mock implements FilterInterface
{
    /**
     * array of log events
     *
     * @var array
     */
    public array $events = [];

    /**
     * Returns TRUE to accept the message
     *
     * @param array $event event data
     */
    public function filter(array $event): bool
    {
        $this->events[] = $event;
        return true;
    }
}
