<?php

declare(strict_types=1);

namespace LaminasTest\Log;

use Laminas\Log\WriterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class WriterPluginManagerTest extends TestCase
{
    /** @var WriterPluginManager */
    protected $plugins;

    public function testInvokableClassFirephp(): void
    {
        $firephp = $this->plugins->get('firephp');
        $this->assertInstanceOf('Laminas\Log\Writer\Firephp', $firephp);
    }

    protected function setUp(): void
    {
        $this->plugins = new WriterPluginManager(new ServiceManager());
    }
}
