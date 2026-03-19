<?php

declare(strict_types=1);

namespace Laminas\Log;

use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function is_array;

class FilterPluginManagerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return FilterPluginManager
     */
    public function __invoke(ContainerInterface $container, $name, ?array $options = null): FilterPluginManager
    {
        $pluginManager = new FilterPluginManager($container, $options ?: []);

        // If this is in a laminas-mvc application, the ServiceListener will inject
        // merged configuration during bootstrap.
        if ($container->has('ServiceListener')) {
            return $pluginManager;
        }

        // If we do not have a config service, nothing more to do
        if (! $container->has('config')) {
            return $pluginManager;
        }

        $config = $container->get('config');

        // If we do not have log_filters configuration, nothing more to do
        if (! isset($config['log_filters']) || ! is_array($config['log_filters'])) {
            return $pluginManager;
        }

        // Wire service configuration for log_filters
        (new Config($config['log_filters']))->configureServiceManager($pluginManager);

        return $pluginManager;
    }
}
