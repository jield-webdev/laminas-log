<?php

declare(strict_types=1);

namespace Laminas\Log;

use Laminas\Log\Filter\LogFilterProviderInterface;
use Laminas\Log\Formatter\LogFormatterProviderInterface;
use Laminas\ModuleManager\Feature\LogProcessorProviderInterface;
use Laminas\ModuleManager\Feature\LogWriterProviderInterface;
use Laminas\ModuleManager\ModuleManager;

class Module
{
    /**
     * Return default laminas-log configuration for laminas-mvc applications.
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();

        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }

    /**
     * Register specifications for all laminas-log plugin managers with the ServiceListener.
     *
     * @param ModuleManager $moduleManager
     */
    public function init($moduleManager): void
    {
        $event           = $moduleManager->getEvent();
        $container       = $event->getParam('ServiceManager');
        $serviceListener = $container->get('ServiceListener');

        $serviceListener->addServiceManager(
            'LogProcessorManager',
            'log_processors',
            LogProcessorProviderInterface::class,
            'getLogProcessorConfig'
        );

        $serviceListener->addServiceManager(
            'LogWriterManager',
            'log_writers',
            LogWriterProviderInterface::class,
            'getLogWriterConfig'
        );

        $serviceListener->addServiceManager(
            'LogFilterManager',
            'log_filters',
            LogFilterProviderInterface::class,
            'getLogFilterConfig'
        );

        $serviceListener->addServiceManager(
            'LogFormatterManager',
            'log_formatters',
            LogFormatterProviderInterface::class,
            'getLogFormatterConfig'
        );
    }
}
