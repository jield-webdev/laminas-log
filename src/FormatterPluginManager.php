<?php

declare(strict_types=1);

namespace Laminas\Log;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;
use function sprintf;

class FormatterPluginManager extends AbstractPluginManager
{
    protected $aliases
        = [
            'base'             => Formatter\Base::class,
            'simple'           => Formatter\Simple::class,
            'xml'              => Formatter\Xml::class,
            'db'               => Formatter\Db::class,
            'errorhandler'     => Formatter\ErrorHandler::class,
            'exceptionhandler' => Formatter\ExceptionHandler::class,
        ];

    protected $factories
        = [
            Formatter\Base::class             => InvokableFactory::class,
            Formatter\Simple::class           => InvokableFactory::class,
            Formatter\Xml::class              => InvokableFactory::class,
            Formatter\Db::class               => InvokableFactory::class,
            Formatter\ErrorHandler::class     => InvokableFactory::class,
            Formatter\ExceptionHandler::class => InvokableFactory::class,
        ];

    protected $instanceOf = Formatter\FormatterInterface::class;

    /**
     * Allow many formatters of the same type (v3)
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
     * @throws Exception\InvalidArgumentException
     */
    public function validatePlugin($plugin): void
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Plugin of type %s is invalid; must implement %s\Formatter\FormatterInterface',
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
    #[\Override]
    public function validate($instance): void
    {
        if (!$instance instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                '%s can only create instances of %s; %s is invalid',
                static::class,
                $this->instanceOf,
                get_debug_type($instance)
            ));
        }
    }
}
