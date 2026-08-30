<?php
declare(strict_types=1);

namespace Elone\Core\Test;

use Elone\Core\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * ConfigurationTest.
 */
#[CoversClass(Configuration::class)]
class ConfigurationTest extends TestCase
{
    /**
     * @link \Elone\Core\Configuration::debug()
     */
    #[Test]
    public function testDebug(): void
    {
        $configuration = new Configuration();
        $result = $configuration->debug();
        $this->assertSame(false, $result);

        $configuration = new Configuration(debug: true);
        $result = $configuration->debug();
        $this->assertSame(true, $result);
    }
}
