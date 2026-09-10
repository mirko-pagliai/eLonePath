<?php
declare(strict_types=1);

namespace Elone\Core\Console;

use FilesystemIterator;
use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Finds every `.php` file under a directory, skipping `vendor/`, `node_modules/`, and `.git/` wherever they occur.
 */
final readonly class PhpFileFinder
{
    /**
     * @var list<string>
     */
    private const array SKIPPED_DIRECTORIES = ['vendor', 'node_modules', '.git'];

    /**
     * @param string $root
     * @return \Generator<string>
     */
    public function find(string $root): Generator
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            if ($this->isSkipped($file->getPathname())) {
                continue;
            }

            yield $file->getPathname();
        }
    }

    /**
     * Checks whether `$path` falls under one of `SKIPPED_DIRECTORIES`.
     *
     * @param string $path
     * @return bool
     */
    private function isSkipped(string $path): bool
    {
        foreach (self::SKIPPED_DIRECTORIES as $skipped) {
            if (str_contains($path, "/$skipped/")) {
                return true;
            }
        }

        return false;
    }
}
