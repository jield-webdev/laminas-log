<?php

declare(strict_types=1);

namespace Laminas\Log;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Override;

use function get_debug_type;
use function sprintf;

class FilterPluginManager extends AbstractPluginManager
{
    protected $aliases
        = [
            'mock'           => Filter\Mock::class,
            'priority'       => Filter\Priority::class,
            'regex'          => Filter\Regex::class,
            'suppress'       => Filter\SuppressFilter::class,
            'suppressfilter' => Filter\SuppressFilter::class,
            'validator'      => Filter\Validator::class,
        ];

    protected $factories
        = [
            Filter\Mock::class           => InvokableFactory::class,
            Filter\Priority::class       => InvokableFactory::class,
            Filter\Regex::class          => InvokableFactory::class,
            Filter\SuppressFilter::class => InvokableFactory::class,
            Filter\Validator::class      => InvokableFactory::class,
        ];

    protected $instanceOf = Filter\FilterInterface::class;

    /**
     * Allow many filters of the same type (v3)
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
                'Plugin of type %s is invalid; must implement %s\Filter\FilterInterface',
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
