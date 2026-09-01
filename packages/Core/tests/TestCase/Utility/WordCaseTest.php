<?php
declare(strict_types=1);

namespace Elone\Core\Test\Utility;

use Elone\Core\Utility\WordCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * WordCaseTest.
 */
#[CoversClass(WordCase::class)]
class WordCaseTest extends TestCase
{
    /**
     * @link \Elone\Core\Utility\WordCase::studlyCase()
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
        $result = new WordCase($raw)->studlyCase();
        $this->assertSame($expected, $result);
    }

    /**
     * @link \Elone\Core\Utility\WordCase::kebabCase()
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
        $result = new WordCase($raw)->kebabCase();
        $this->assertSame($expected, $result);
    }
}
