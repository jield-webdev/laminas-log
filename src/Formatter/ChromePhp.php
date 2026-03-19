<?php

declare(strict_types=1);

namespace Laminas\Log\Formatter;

class ChromePhp implements FormatterInterface
{
    /**
     * Formats the given event data into a single line to be written by the writer.
     *
     * @param array $event The event data which should be formatted.
     */
    public function format($event): string
    {
        return $event['message'];
    }

    /**
     * This method is implemented for FormatterInterface but not used.
     */
    public function getDateTimeFormat(): string
    {
        return '';
    }

    /**
     * This method is implemented for FormatterInterface but not used.
     *
     * @param string $dateTimeFormat
     */
    public function setDateTimeFormat($dateTimeFormat): FormatterInterface
    {
        return $this;
    }
}
