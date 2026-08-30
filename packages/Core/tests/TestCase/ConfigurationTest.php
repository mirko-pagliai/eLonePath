<?php
declare(strict_types=1);

namespace Elone\Core\Test;

use Elone\Core\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * ConfigurationTest.
 */
#[CoversClass(Configuration::class)]
class ConfigurationTest extends TestCase
{
    /**
     * @link \Elone\Core\Configuration::rootPath()
     */
    #[Test]
    #[TestWith([TEST_APP])]
    #[TestWith(['/tmp/'])]
    public function testRootPath(string $rootPath): void
    {
        $configuration = new Configuration(rootPath: $rootPath, controllerNamespace: 'TestApp');
        $result = $configuration->rootPath();
        $this->assertSame('/tmp/', $result);
    }

    /**
     * @link \Elone\Core\Configuration::templatesPath()
     */
    #[Test]
    public function testTemplatesPath(): void
    {
        $configuration = new Configuration(rootPath: TEST_APP, controllerNamespace: 'TestApp');
        $result = $configuration->templatesPath();
        $this->assertSame('/tmp/templates/', $result);
    }

    /**
     * @link \Elone\Core\Configuration::controllerNamespace()
     */
    #[Test]
    public function testControllerNamespace(): void
    {
        $configuration = new Configuration(rootPath: TEST_APP, controllerNamespace: 'TestApp');
        $result = $configuration->controllerNamespace();
        $this->assertSame('TestApp', $result);
    }

    /**
     * @link \Elone\Core\Configuration::debug()
     */
    #[Test]
    public function testDebug(): void
    {
        $configuration = new Configuration(rootPath: TEST_APP, controllerNamespace: 'TestApp');
        $result = $configuration->debug();
        $this->assertSame(false, $result);

        $configuration = new Configuration(rootPath: TEST_APP, controllerNamespace: 'TestApp', debug: true);
        $result = $configuration->debug();
        $this->assertSame(true, $result);
    }
}
