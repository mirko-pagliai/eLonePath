<?php
declare(strict_types=1);

namespace Elone\Core\Test\Routing;

use Elone\Core\Routing\ControllerName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * ControllerNameTest.
 */
#[CoversClass(ControllerName::class)]
class ControllerNameTest extends TestCase
{
    /**
     * @link \Elone\Core\Routing\ControllerName::studlyCase()
     */
    #[Test]
    #[TestWith(['Pages', 'Pages'])]
    #[TestWith(['pages', 'Pages'])]
    #[TestWith(['UsersSettings', 'UsersSettings'])]
    #[TestWith(['users-settings', 'UsersSettings'])]
    #[TestWith(['users_settings', 'UsersSettings'])]
    #[TestWith(['', ''])]
    public function testStudlyCase(string $raw, string $expected): void
    {
        $result = new ControllerName($raw)->studlyCase();
        $this->assertSame($expected, $result);
    }

    /**
     * @link \Elone\Core\Routing\ControllerName::kebabCase()
     */
    #[Test]
    #[TestWith(['Pages', 'pages'])]
    #[TestWith(['pages', 'pages'])]
    #[TestWith(['UsersSettings', 'users-settings'])]
    #[TestWith(['users-settings', 'users-settings'])]
    #[TestWith(['users_settings', 'users-settings'])]
    #[TestWith(['', ''])]
    public function testKebabCase(string $raw, string $expected): void
    {
        $result = new ControllerName($raw)->kebabCase();
        $this->assertSame($expected, $result);
    }

    /**
     * Documents a known limitation rather than fixing it: a run of consecutive uppercase letters (a fully
     * upper-cased acronym) is split one letter at a time instead of being treated as a single word, affecting both
     * `studlyCase()` and `kebabCase()`. Left as-is because this project's naming convention — matching CakePHP's own
     * coding standard — always writes an acronym as a single capitalized word (`Api`, never `API`), so the ambiguous
     * input never occurs in practice.
     *
     * @link \Elone\Core\Routing\ControllerName::studlyCase()
     * @link \Elone\Core\Routing\ControllerName::kebabCase()
     */
    #[Test]
    public function testDoesNotMergeAllCapsRuns(): void
    {
        $controllerName = new ControllerName('API');
        $this->assertSame('API', $controllerName->studlyCase());
        $this->assertSame('a-p-i', $controllerName->kebabCase());
    }
}
