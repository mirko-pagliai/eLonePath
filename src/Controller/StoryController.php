<?php
declare(strict_types=1);

namespace App\Controller;

use App\Story\DiceNode;
use App\Story\Game;
use App\Utility\Dice;
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
        $file = ROOT . "/resources/stories/$storyId/story.json";

        $game = Game::createFromFile($file);
        $node = $game->getNode($nodeNumber);

        $this->set(compact('game', 'node'));
    }

    /**
     * Rolls the dice for a `DiceNode` and shows the outcome, with a link to whichever node it points to.
     *
     * @param string $storyId
     * @param int $nodeNumber
     * @return void
     * @link templates/story/roll.php
     */
    public function roll(string $storyId, int $nodeNumber): void
    {
        $file = ROOT . "/resources/stories/$storyId/story.json";

        $game = Game::createFromFile($file);
        $node = $game->getNode($nodeNumber);

        if (!$node instanceof DiceNode) {
            throw new RuntimeException("Node `$nodeNumber` in `$storyId` is not a dice check.");
        }

        $rolls = new Dice()->rollMultiple($node->requiredRolls);
        $total = array_sum($rolls);
        $success = $node->isSuccess($total);
        $target = $node->targetFor($total);

        $this->set(compact('game', 'rolls', 'total', 'success', 'target'));
    }
}
