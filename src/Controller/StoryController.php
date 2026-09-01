<?php
declare(strict_types=1);

namespace App\Controller;

use App\Story\Game;
use App\Story\Nodes\DiceNode;
use App\Utility\Dice;
use Elone\Core\Controller;
use RuntimeException;

class StoryController extends Controller
{
    /**
     * @param string $storyId The identifier of the story.
     * @return \App\Story\Game The game instance created from the specified story file.
     */
    protected function getGame(string $storyId): Game
    {
        return Game::createFromFile(STORIES . "/$storyId/story.json");
    }

    /**
     * @param string $storyId
     * @param int $nodeNumber
     * @return void
     * @link templates/story/chapter.php
     */
    public function chapter(string $storyId, int $nodeNumber): void
    {
        $game = $this->getGame($storyId);
        $node = $game->getNode($nodeNumber);

        $this->set(compact('game', 'node'));
    }

    /**
     * Rolls the dice for a `DiceNode` and shows the outcome, with a link to whichever node it points to.
     *
     * @param string $storyId
     * @param int $nodeNumber
     * @return void
     * @throws \Random\RandomException
     * @link templates/story/roll.php
     */
    public function roll(string $storyId, int $nodeNumber): void
    {
        $game = $this->getGame($storyId);
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
