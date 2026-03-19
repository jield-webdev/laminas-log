<?php

declare(strict_types=1);

namespace Laminas\Log\Writer;

use Laminas\Log\Exception;
use Laminas\Log\Formatter\Simple as SimpleFormatter;
use Laminas\Log\Logger;
use Traversable;
use function array_key_exists;
use function closelog;
use function constant;
use function defined;
use function in_array;
use function is_array;
use function iterator_to_array;
use function openlog;
use function stripos;
use function syslog;
use const LOG_ALERT;
use const LOG_CRIT;
use const LOG_DEBUG;
use const LOG_EMERG;
use const LOG_ERR;
use const LOG_INFO;
use const LOG_NOTICE;
use const LOG_PID;
use const LOG_USER;
use const LOG_WARNING;
use const PHP_OS_FAMILY;

/**
 * Writes log messages to syslog
 */
class Syslog extends AbstractWriter
{
    /**
     * Last application name set by a syslog-writer instance
     */
    protected static string $lastApplication;

    /**
     * Last facility name set by a syslog-writer instance
     */
    protected static int $lastFacility;

    /**
     * Maps Laminas\Log priorities to PHP's syslog priorities
     *
     * @var array
     */
    protected array $priorities
        = [
            Logger::EMERG  => LOG_EMERG,
            Logger::ALERT  => LOG_ALERT,
            Logger::CRIT   => LOG_CRIT,
            Logger::ERR    => LOG_ERR,
            Logger::WARN   => LOG_WARNING,
            Logger::NOTICE => LOG_NOTICE,
            Logger::INFO   => LOG_INFO,
            Logger::DEBUG  => LOG_DEBUG,
        ];

    /**
     * The default log priority - for unmapped custom priorities
     */
    protected int $defaultPriority = LOG_NOTICE;

    /**
     * Application name used by this syslog-writer instance
     */
    protected string $appName = 'Laminas\Log';

    /**
     * Facility used by this syslog-writer instance
     */
    protected int $facility = LOG_USER;

    /**
     * Types of program available to logging of message
     */
    protected array $validFacilities = [];

    /**
     * Constructor
     *
     * @param array $params Array of options; may include "application" and "facility" keys
     */
    public function __construct($params = null)
    {
        if ($params instanceof Traversable) {
            $params = iterator_to_array($params);
        }

        $runInitializeSyslog = true;

        if (is_array($params)) {
            parent::__construct($params);

            if (isset($params['application'])) {
                $this->appName = $params['application'];
            }

            if (isset($params['facility'])) {
                $this->setFacility($params['facility']);
                $runInitializeSyslog = false;
            }
        }

        if ($runInitializeSyslog) {
            $this->initializeSyslog();
        }

        if ($this->formatter === null) {
            $this->setFormatter(new SimpleFormatter('%message%'));
        }
    }

    /**
     * Set syslog facility
     *
     * @param int $facility Syslog facility
     * @return Syslog
     * @throws Exception\InvalidArgumentException For invalid log facility.
     */
    public function setFacility($facility): static
    {
        if ($this->facility === $facility) {
            return $this;
        }

        if ($this->validFacilities === []) {
            $this->initializeValidFacilities();
        }

        if (!in_array($facility, $this->validFacilities)) {
            throw new Exception\InvalidArgumentException(
                'Invalid log facility provided; please see http://php.net/openlog for a list of valid facility values'
            );
        }

        if (
            0 === stripos(PHP_OS_FAMILY, 'WIN')
            && ($facility !== LOG_USER)
        ) {
            throw new Exception\InvalidArgumentException(
                'Only LOG_USER is a valid log facility on Windows'
            );
        }

        $this->facility = $facility;
        $this->initializeSyslog();
        return $this;
    }

    /**
     * Initialize values facilities
     *
     * @return void
     */
    protected function initializeValidFacilities(): void
    {
        $constants = [
            'LOG_AUTH',
            'LOG_AUTHPRIV',
            'LOG_CRON',
            'LOG_DAEMON',
            'LOG_KERN',
            'LOG_LOCAL0',
            'LOG_LOCAL1',
            'LOG_LOCAL2',
            'LOG_LOCAL3',
            'LOG_LOCAL4',
            'LOG_LOCAL5',
            'LOG_LOCAL6',
            'LOG_LOCAL7',
            'LOG_LPR',
            'LOG_MAIL',
            'LOG_NEWS',
            'LOG_SYSLOG',
            'LOG_USER',
            'LOG_UUCP',
        ];

        foreach ($constants as $constant) {
            if (defined($constant)) {
                $this->validFacilities[] = constant($constant);
            }
        }
    }

    /**
     * Initialize syslog / set application name and facility
     *
     * @return void
     */
    protected function initializeSyslog(): void
    {
        static::$lastApplication = $this->appName;
        static::$lastFacility    = $this->facility;
        openlog($this->appName, LOG_PID, $this->facility);
    }

    /**
     * Set application name
     *
     * @param string $appName Application name
     * @return Syslog
     */
    public function setApplicationName($appName): static
    {
        if ($this->appName === $appName) {
            return $this;
        }

        $this->appName = $appName;
        $this->initializeSyslog();
        return $this;
    }

    /**
     * Close syslog.
     *
     * @return void
     */
    public function shutdown(): void
    {
        closelog();
    }

    /**
     * Write a message to syslog.
     *
     * @param array $event event data
     * @return void
     */
    protected function doWrite(array $event): void
    {
        if (array_key_exists($event['priority'], $this->priorities)) {
            $priority = $this->priorities[$event['priority']];
        } else {
            $priority = $this->defaultPriority;
        }

        if (
            $this->appName !== static::$lastApplication
            || $this->facility !== static::$lastFacility
        ) {
            $this->initializeSyslog();
        }

        $message = $this->formatter->format($event);

        syslog($priority, $message);
    }
}
