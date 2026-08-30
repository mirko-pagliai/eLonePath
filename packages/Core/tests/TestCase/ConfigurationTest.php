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
    #[TestWith([TEST_APP, TEST_APP])]
    #[TestWith(['/tmp/', '/tmp'])]
    #[TestWith(['/tmp/', '/tmp/'])]
    public function testRootPath(string $expectedRootPath, string $rootPath): void
    {
        $configuration = new Configuration(rootPath: $rootPath, namespace: 'TestApp');
        $result = $configuration->rootPath();
        $this->assertSame($expectedRootPath, $result);
    }

    /**
     * @link \Elone\Core\Configuration::templatesPath()
     */
    #[Test]
    public function testTemplatesPath(): void
    {
        $configuration = new Configuration(rootPath: TEST_APP, namespace: 'TestApp');
        $result = $configuration->templatesPath();
        $this->assertSame(TEST_APP . 'templates/', $result);
    }

    /**
     * @link \Elone\Core\Configuration::namespace()
     */
    #[Test]
    public function testNamespace(): void
    {
        $configuration = new Configuration(rootPath: TEST_APP, namespace: 'TestApp');
        $result = $configuration->namespace();
        $this->assertSame('TestApp', $result);
    }

    /**
     * @link \Elone\Core\Configuration::debug()
     */
    #[Test]
    public function testDebug(): void
    {
        $configuration = new Configuration(rootPath: TEST_APP, namespace: 'TestApp');
        $result = $configuration->debug();
        $this->assertSame(false, $result);

        $configuration = new Configuration(rootPath: TEST_APP, namespace: 'TestApp', debug: true);
        $result = $configuration->debug();
        $this->assertSame(true, $result);
    }
}
