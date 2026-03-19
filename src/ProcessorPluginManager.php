<?php

declare(strict_types=1);

namespace Laminas\Log;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Override;

use function get_debug_type;
use function sprintf;

/**
 * Plugin manager for log processors.
 */
class ProcessorPluginManager extends AbstractPluginManager
{
    protected $aliases
        = [
            'backtrace'      => Processor\Backtrace::class,
            'psrplaceholder' => Processor\PsrPlaceholder::class,
            'referenceid'    => Processor\ReferenceId::class,
            'requestid'      => Processor\RequestId::class,
        ];

    protected $factories
        = [
            Processor\Backtrace::class      => InvokableFactory::class,
            Processor\PsrPlaceholder::class => InvokableFactory::class,
            Processor\ReferenceId::class    => InvokableFactory::class,
            Processor\RequestId::class      => InvokableFactory::class,
        ];

    protected $instanceOf = Processor\ProcessorInterface::class;

    /**
     * Allow many processors of the same type (v3)
     *
     * @var bool
     */
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
                'Plugin of type %s is invalid; must implement %s\Processor\ProcessorInterface',
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
