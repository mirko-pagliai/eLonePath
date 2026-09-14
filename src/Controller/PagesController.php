<?php
declare(strict_types=1);

namespace App\Controller;

use App\Story\Game;
use LogicException;
use Symfony\Component\Finder\Finder;

/**
 * Handles the pages-related actions within the application.
 */
final class PagesController extends AppController
{
    /**
     * Handles the logic for the home functionality.
     *
     * @return void
     * @link templates/Pages/home.php
     */
    public function home(): void
    {
    }

    /**
     * Scans for documentation files, processes their content, and extracts relevant metadata.
     *
     * @return void
     * @link templates/Pages/docs.php
     */
    public function docs(): void
    {
        $files = glob(DOCS . '/it/*.md') ?: [];

        $files = array_map(
            callback: function (string $path): array {
                $content = file_get_contents($path) ?: '';
                if (trim($content) === '') {
                    throw new LogicException("The content of `$path` is empty.");
                }

                $firstLine = strtok($content, "\n");
                if (!$firstLine || !str_starts_with($firstLine, '# ')) {
                    throw new LogicException("The first line of `$path` is not a valid title.");
                }
                $title = substr($firstLine, 2);

                return [
                    'basename' => basename($path, '.md'),
                ] + compact('title');
            },
            array: $files,
        );

        $this->set(compact('files'));
    }

    /**
     * Loads and processes documentation content based on the provided basename.
     *
     * @param string $basename The base name of the documentation file to load.
     * @return void
     * @throws \LogicException If the basename contains invalid characters or if the specified file does not exist.
     * @link templates/Pages/docs.php
     */
    public function doc(string $basename): void
    {
        foreach (['.', '/', '\\'] as $char) {
            if (str_contains($basename, $char)) {
                throw new LogicException("The basename `$basename` contains invalid characters.");
            }
        }

        $dir = DOCS . '/it';

        $file = $dir . '/' . $basename . '.md';
        if (!file_exists($file)) {
            throw new LogicException("The file `$file` does not exist.");
        }

        $content = file_get_contents($file);

        $this->set(compact('content'));
    }

    /**
     * Retrieves and processes stories.
     *
     * @return void
     * @link templates/Pages/stories.php
     */
    public function stories(): void
    {
        $finder = new Finder();
        $finder->in(STORIES)
            ->name('story.json')
            ->files();

        /** @var array<\Symfony\Component\Finder\SplFileInfo> $files */
        $files = iterator_to_array($finder);

        $stories = [];

        foreach ($files as $file) {
            $stories[] = Game::createFromFile($file->getRealPath());
        }

        $this->set(compact('stories'));
    }
}
