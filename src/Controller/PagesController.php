<?php
declare(strict_types=1);

namespace App\Controller;

use App\Exception\DocumentNotFoundException;
use App\Story\Game;
use Elone\Core\Translator;
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
     * Lists the documentation pages available in the current request's own locale, each with the title its
     * first line declares.
     *
     * @return void
     * @throws \LogicException If a documentation file is empty, or its first line isn't a valid `# Title`.
     * @link templates/Pages/docs.php
     */
    public function docs(): void
    {
        $locale = Translator::getLocale();

        $files = glob(DOCS . "/$locale/*.md") ?: [];

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
     * Loads and shows a single documentation page, from the current request's own locale.
     *
     * @param string $basename The base name of the documentation file to load.
     * @return void
     * @throws \App\Exception\DocumentNotFoundException If `$basename` contains anything other than letters,
     * digits, hyphens or underscores, or if no file matches it in the current locale.
     * @link templates/Pages/doc.php
     */
    public function doc(string $basename): void
    {
        if (preg_match('/^[A-Za-z0-9_-]+$/', $basename) !== 1) {
            throw new DocumentNotFoundException("The basename `$basename` contains invalid characters.");
        }

        $locale = Translator::getLocale();

        $file = DOCS . "/$locale/$basename.md";
        if (!file_exists($file)) {
            throw new DocumentNotFoundException("The file `$file` does not exist.");
        }

        $content = file_get_contents($file) ?: '';

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
