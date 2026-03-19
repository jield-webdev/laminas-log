<?php

declare(strict_types=1);

namespace Laminas\Log;

use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function is_array;

class WriterPluginManagerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return WriterPluginManager
     */
    public function __invoke(ContainerInterface $container, $name, ?array $options = null): WriterPluginManager
    {
        $pluginManager = new WriterPluginManager($container, $options ?: []);

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

        // If we do not have log_writers configuration, nothing more to do
        if (! isset($config['log_writers']) || ! is_array($config['log_writers'])) {
            return $pluginManager;
        }

        // Wire service configuration for log_writers
        (new Config($config['log_writers']))->configureServiceManager($pluginManager);

        return $pluginManager;
    }
}
