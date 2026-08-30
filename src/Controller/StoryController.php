<?php
declare(strict_types=1);

namespace App\Controller;

use App\Story\Game;
use Elone\Core\Controller;

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
        $file = ROOT . "/resources/stories/$storyId/story.json";

        $game = Game::createFromFile($file);
        $node = $game->getNode($nodeNumber);

        $this->set(compact('game', 'node'));
    }
}
