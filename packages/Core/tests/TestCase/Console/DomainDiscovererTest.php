<?php
declare(strict_types=1);

namespace Elone\Core\Test\TestCase\Console;

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
     * `'default'` is always present, even though none of the fixtures call `__d('default', ...)` explicitly —
     * it's what a bare `__()` call is assigned to, so `PhpScanner` always needs it declared. `i18n-two.php`'s
     * non-literal `__()` call contributes no domain: there's nothing to read a name from.
     *
     * @link \Elone\Core\Console\DomainDiscoverer::discover()
     */
    #[Test]
    public function testDiscoverFindsEveryLiteralDomainPlusDefault(): void
    {
        $fixtures = TEST_APP . '/templates';
        $discoverer = new DomainDiscoverer();

        $domains = $discoverer->discover([
            "$fixtures/i18n-one.php",
            "$fixtures/i18n-two.php",
            "$fixtures/i18n-plural.php",
        ]);
        sort($domains);

        $this->assertSame(['default', 'errors', 'validation'], $domains);
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
