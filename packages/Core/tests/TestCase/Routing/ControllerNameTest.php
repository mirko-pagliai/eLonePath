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
    #[TestWith(['API', 'API'])]
    #[TestWith(['APIUsers', 'APIUsers'])]
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
    #[TestWith(['API', 'api'])]
    #[TestWith(['APIUsers', 'api-users'])]
    #[TestWith(['', ''])]
    public function testKebabCase(string $raw, string $expected): void
    {
        $result = new ControllerName($raw)->kebabCase();
        $this->assertSame($expected, $result);
    }
}
