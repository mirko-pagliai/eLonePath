<?php
declare(strict_types=1);

namespace App\Controller;

use Elone\Core\Controller;
use Symfony\Component\Finder\Finder;

/**
 * Handles the pages-related actions within the application.
 */
final class PagesController extends Controller
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
        $finder->in($this->getConfiguration()->rootPath() . 'resources/stories/')
            ->name('story.json')
            ->files();

        /** @var array<\Symfony\Component\Finder\SplFileInfo> $files */
        $files = iterator_to_array($finder);

        $stories = [];

        foreach ($files as $file) {
            $contents = file_get_contents($file->getRealPath());
            if ($contents === false) {
                continue;
            }

            $json = json_decode($contents);
            if (!is_object($json)) {
                continue;
            }

            $stories[] = $json->game;
        }

        $this->set(compact('stories'));
    }
}
