<?php
declare(strict_types=1);

namespace Elone\Core\Test\Console;

use Elone\Core\Console\DomainDiscoverer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * DomainDiscovererTest.
 */
#[CoversClass(DomainDiscoverer::class)]
class DomainDiscovererTest extends TestCase
{
    /**
     * `'default'` is always present, even though neither fixture calls `__d('default', ...)` explicitly — it's
     * what a bare `__()` call is assigned to, so `PhpScanner` always needs it declared. `Two.php`'s non-literal
     * `__d()` call contributes no domain: there's nothing to read a name from.
     *
     * @link \Elone\Core\Console\DomainDiscoverer::discover()
     */
    #[Test]
    public function testDiscoverFindsEveryLiteralDomainPlusDefault(): void
    {
        $fixtures = __DIR__ . '/../../test_app/i18n';
        $discoverer = new DomainDiscoverer();

        $domains = $discoverer->discover(["$fixtures/One.php", "$fixtures/Two.php"]);
        sort($domains);

        $this->assertSame(['default', 'validation'], $domains);
    }

    /**
     * @link \Elone\Core\Console\DomainDiscoverer::discover()
     */
    #[Test]
    public function testDiscoverWithNoFilesReturnsOnlyDefault(): void
    {
        $discoverer = new DomainDiscoverer();

        $this->assertSame(['default'], $discoverer->discover([]));
    }
}
