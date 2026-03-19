<?php

declare(strict_types=1);

namespace Laminas\Log;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Logger abstract service factory.
 *
 * Allow to configure multiple loggers for application.
 */
class LoggerAbstractServiceFactory extends LoggerServiceFactory implements AbstractFactoryInterface
{
    /** @var array */
    protected array $config = [];

    public function __construct(protected string $configKey = 'log')
    {
    }

    /**
     * @param string $requestedName
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        $config = $this->getConfig($container);
        if ($config === []) {
            return false;
        }

        return isset($config[$requestedName]);
    }

    /**
     * Retrieve configuration for loggers, if any
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getConfig(ContainerInterface $services): array
    {
        if (!empty($this->config)) {
            return $this->config;
        }

        if (!$services->has('config')) {
            $this->config = [];

            return $this->config;
        }

        $config = $services->get('config');
        if (!isset($config[$this->configKey])) {
            $this->config = [];

            return $this->config;
        }

        $this->config = $config[$this->configKey];

        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Logger
    {
        $config = $this->getConfig($container);
        $config = $config[$requestedName];

        $this->processConfig($config, $container);

        return new Logger($config);
    }

}
