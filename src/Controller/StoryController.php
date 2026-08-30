<?php
declare(strict_types=1);

namespace App\Controller;

use App\Story\Game;
use Elone\Core\Controller;
use RuntimeException;

class StoryController extends Controller
{
    /**
     * @param string $storyId
     * @param int $nodeNumber
     * @return void
     * @link templates/story/chapter.php
     */
    public function chapter(string $storyId, int $nodeNumber): void
    {
        $file = $this->getConfiguration()->rootPath() . "resources/stories/$storyId/story.json";

        $contents = file_get_contents($file);
        if ($contents === false) {
            throw new RuntimeException("Failed to read `$file`.");
        }

        $json = json_decode($contents, true);
        if (!is_array($json)) {
            throw new RuntimeException("Failed to parse `$file`.");
        }

        $game = Game::createFromArray($json);
        $node = $game->getNode($nodeNumber);

        $this->set(compact('game', 'node'));
    }
}
