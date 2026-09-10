<?php
declare(strict_types=1);

namespace Elone\Core\Test\Console;

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
    private string $root;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir() . '/php-file-finder-test-' . uniqid();

        mkdir("$this->root/src", recursive: true);
        mkdir("$this->root/vendor/somepackage", recursive: true);
        mkdir("$this->root/node_modules/somemodule", recursive: true);
        mkdir("$this->root/.git/objects", recursive: true);
        mkdir("$this->root/templates", recursive: true);

        touch("$this->root/src/File1.php");
        touch("$this->root/src/File2.php");
        touch("$this->root/templates/view.php");
        touch("$this->root/templates/readme.md");
        touch("$this->root/vendor/somepackage/Should.php");
        touch("$this->root/node_modules/somemodule/Should2.php");
        touch("$this->root/.git/objects/abc123");
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->removeDirectory($this->root);
    }

    /**
     * Removes `$dir` and everything in it.
     *
     * @param string $dir
     * @return void
     */
    private function removeDirectory(string $dir): void
    {
        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = "$dir/$item";
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }

    /**
     * @link \Elone\Core\Console\PhpFileFinder::find()
     */
    #[Test]
    public function testFindReturnsOnlyPhpFilesOutsideSkippedDirectories(): void
    {
        $finder = new PhpFileFinder();

        $found = iterator_to_array($finder->find($this->root), false);
        $relative = array_map(fn(string $path): string => str_replace("$this->root/", '', $path), $found);
        sort($relative);

        $this->assertSame(['src/File1.php', 'src/File2.php', 'templates/view.php'], $relative);
    }
}
