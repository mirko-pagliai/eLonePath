<?php
declare(strict_types=1);

namespace Elone\Core\Console;

use FilesystemIterator;
use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Finds every `.php` file under `$root`'s own `src/` and `templates/` subdirectories — the two places
 * translatable strings actually live. Anything else under `$root` (`vendor/`, `tests/`, `resources/`, and so
 * on) is never looked at in the first place, rather than walked and then excluded by name.
 */
final readonly class PhpFileFinder
{
    /**
     * @var list<string>
     */
    private const array DIRECTORIES = ['src', 'templates'];

    /**
     * @param string $root
     * @return \Generator<string>
     */
    public function find(string $root): Generator
    {
        foreach (self::DIRECTORIES as $directory) {
            $path = "$root/$directory";

            if (!is_dir($path)) {
                continue;
            }

            yield from $this->findInDirectory($path);
        }
    }

    /**
     * @param string $directory
     * @return \Generator<string>
     */
    private function findInDirectory(string $directory): Generator
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            yield $file->getPathname();
        }
    }
}
