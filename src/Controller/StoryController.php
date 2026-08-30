<?php
declare(strict_types=1);

namespace App\Controller;

use App\Story\Node;
use Elone\Core\Controller;
use RuntimeException;

class StoryController extends Controller
{
    /**
     * @param int $node
     * @return void
     * @link templates/story/chapter.php
     */
    public function chapter(string $storyId, int $nodeNumber): void
    {
        $dir = $this->getConfiguration()->rootPath() . "resources/stories/$storyId/";

        $contents = file_get_contents($dir . 'story.json');
        if ($contents === false) {
            throw new RuntimeException('Failed to read story.json');
        }

        $json = json_decode($contents);
        if (!is_object($json)) {
            throw new RuntimeException('Failed to parse story.json');
        }

        $game = $json->game;
        $nodeFromJson = $json->nodes->{$nodeNumber};

        $node = new Node(
            id: $nodeNumber,
            gameId: $game->id,
            content: $nodeFromJson->content,
            choices: $nodeFromJson->choices,
        );

        $this->set([
            'node' => $node,
            'game' => $game,
        ]);
    }
}
