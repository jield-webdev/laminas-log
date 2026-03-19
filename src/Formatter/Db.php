<?php

declare(strict_types=1);

namespace Laminas\Log\Formatter;

use DateTime;
use Traversable;

use function array_walk_recursive;
use function is_array;
use function iterator_to_array;

class Db implements FormatterInterface
{
    /**
     * Format specifier for DateTime objects in event data (default: ISO 8601)
     *
     * @see http://php.net/manual/en/function.date.php
     *
     * @var string
     */
    protected string $dateTimeFormat = self::DEFAULT_DATETIME_FORMAT;

    /**
     * @see http://php.net/manual/en/function.date.php
     *
     * @param null|string $dateTimeFormat Format specifier for DateTime objects in event data
     */
    public function __construct($dateTimeFormat = null)
    {
        if ($dateTimeFormat instanceof Traversable) {
            $dateTimeFormat = iterator_to_array($dateTimeFormat);
        }

        if (is_array($dateTimeFormat)) {
            $dateTimeFormat = $dateTimeFormat['dateTimeFormat'] ?? null;
        }

        if (null !== $dateTimeFormat) {
            $this->setDateTimeFormat($dateTimeFormat);
        }
    }

    /**
     * Formats data to be written by the writer.
     *
     * @param array $event event data
     * @return array
     */
    public function format($event): array
    {
        $format = $this->getDateTimeFormat();
        array_walk_recursive($event, function (&$value) use ($format) {
            if ($value instanceof DateTime) {
                $value = $value->format($format);
            }
        });

        return $event;
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
}
