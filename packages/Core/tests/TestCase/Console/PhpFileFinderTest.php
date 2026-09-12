<?php
declare(strict_types=1);

namespace Elone\Core\Test\TestCase\Console;

use Elone\Core\Console\PhpFileFinder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * PhpFileFinderTest.
 */
#[CoversClass(PhpFileFinder::class)]
class PhpFileFinderTest extends TestCase
{
    /**
     * `TEST_APP` already has real `src/` and `templates/` subdirectories, used by other tests — no need to
     * build a throwaway directory tree just to prove `find()` walks both of them.
     *
     * @link \Elone\Core\Console\PhpFileFinder::find()
     */
    #[Test]
    public function testFindLooksUnderSrcAndTemplates(): void
    {
        $finder = new PhpFileFinder();

        $found = iterator_to_array($finder->find(TEST_APP), false);
        $relative = array_map(fn(string $path): string => str_replace(TEST_APP . '/', '', $path), $found);

        $this->assertContains('src/Controller/PagesController.php', $relative);
        $this->assertContains('templates/Pages/home.php', $relative);
    }

    /**
     * `TEST_APP/resources` is a real directory (it holds the `.po` fixtures other tests use), but has neither a
     * `src/` nor a `templates/` subdirectory of its own — nothing to walk, but not an error either.
     *
     * @link \Elone\Core\Console\PhpFileFinder::find()
     */
    #[Test]
    public function testFindWithNeitherDirectoryReturnsNothing(): void
    {
        $finder = new PhpFileFinder();

        $found = iterator_to_array($finder->find(TEST_APP . '/resources'), false);

        $this->assertSame([], $found);
    }
}
