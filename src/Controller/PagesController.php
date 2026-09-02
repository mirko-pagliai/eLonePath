<?php
declare(strict_types=1);

namespace App\Controller;

use App\Story\Game;
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
     * @link templates/pages/home.php
     */
    public function home(): void
    {
    }

    /**
     * Retrieves and processes stories.
     *
     * @return void
     * @link templates/pages/stories.php
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
