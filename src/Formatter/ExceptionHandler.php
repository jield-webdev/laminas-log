<?php

declare(strict_types=1);

namespace Laminas\Log\Formatter;

use DateTime;

use function print_r;
use function sprintf;

use const PHP_EOL;

class ExceptionHandler implements FormatterInterface
{
    /**
     * Format specifier for DateTime objects in event data
     *
     * @see http://php.net/manual/en/function.date.php
     *
     * @var string
     */
    protected string $dateTimeFormat = self::DEFAULT_DATETIME_FORMAT;

    /**
     * This method formats the event for the PHP Exception
     *
     * @param array $event
     */
    public function format($event): string
    {
        if (isset($event['timestamp']) && $event['timestamp'] instanceof DateTime) {
            $event['timestamp'] = $event['timestamp']->format($this->getDateTimeFormat());
        }

        $output = $event['timestamp'] . ' ' . $event['priorityName'] . ' ('
            . $event['priority'] . ') ' . $event['message'] . ' in '
            . $event['extra']['file'] . ' on line ' . $event['extra']['line'];

        if (! empty($event['extra']['trace'])) {
            $outputTrace = '';
            foreach ($event['extra']['trace'] as $trace) {
                $outputTrace .= sprintf('File  : %s%s', $trace['file'], PHP_EOL)
                    . sprintf('Line  : %s%s', $trace['line'], PHP_EOL)
                    . sprintf('Func  : %s%s', $trace['function'], PHP_EOL)
                    . sprintf('Class : %s%s', $trace['class'], PHP_EOL)
                    . "Type  : " . $this->getType($trace['type']) . "\n"
                    . "Args  : " . print_r($trace['args'], true) . "\n";
            }

            $output .= "\n[Trace]\n" . $outputTrace;
        }

        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormat(): string
    {
        return $this->dateTimeFormat;
    }

    /**
     * {@inheritDoc}
     */
    public function setDateTimeFormat($dateTimeFormat): FormatterInterface|static
    {
        $this->dateTimeFormat = (string) $dateTimeFormat;
        return $this;
    }

    /**
     * Get the type of a function
     *
     * @param string $type
     */
    protected function getType($type): string
    {
        return match ($type) {
            "::"    => "static",
            "->"    => "method",
            default => $type,
        };
    }
}
