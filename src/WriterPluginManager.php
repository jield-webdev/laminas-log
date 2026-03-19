<?php

declare(strict_types=1);

namespace Laminas\Log;

use Laminas\Log\Writer\Factory\WriterFactory;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Override;

use function get_debug_type;
use function sprintf;

/**
 * Plugin manager for log writers.
 */
class WriterPluginManager extends AbstractPluginManager
{
    protected $aliases
        = [
            'fingerscrossed' => Writer\FingersCrossed::class,
            'firephp'        => Writer\FirePhp::class,
            'mail'           => Writer\Mail::class,
            'mock'           => Writer\Mock::class,
            'noop'           => Writer\Noop::class,
            'psr'            => Writer\Psr::class,
            'stream'         => Writer\Stream::class,
            'syslog'         => Writer\Syslog::class,
        ];

    protected $factories
        = [
            Writer\FirePhp::class        => WriterFactory::class,
            Writer\Mail::class           => WriterFactory::class,
            Writer\Mock::class           => WriterFactory::class,
            Writer\Noop::class           => WriterFactory::class,
            Writer\Psr::class            => WriterFactory::class,
            Writer\Stream::class         => WriterFactory::class,
            Writer\Syslog::class         => WriterFactory::class,
            Writer\FingersCrossed::class => WriterFactory::class,
        ];

    protected $instanceOf = Writer\WriterInterface::class;

    protected $sharedByDefault = false;

    /**
     * Validate the plugin is of the expected type (v2).
     *
     * Proxies to `validate()`.
     *
     * @param mixed $plugin
     * @throws InvalidServiceException
     */
    public function validatePlugin($plugin): void
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Plugin of type %s is invalid; must implement %s\Writer\WriterInterface',
                get_debug_type($plugin),
                __NAMESPACE__
            ));
        }
    }

    /**
     * Validate the plugin is of the expected type (v3).
     *
     * Validates against `$instanceOf`.
     *
     * @param mixed $instance
     * @throws InvalidServiceException
     */
    #[Override]
    public function validate($instance): void
    {
        if (! $instance instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                '%s can only create instances of %s; %s is invalid',
                static::class,
                $this->instanceOf,
                get_debug_type($instance)
            ));
        }
    }
}
