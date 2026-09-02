<?php
declare(strict_types=1);

namespace Elone\Debugger;

use App\Story\Game;

readonly class NodeImagesWalker
{
    public function __construct(protected Game $game)
    {
    }

    /**
     * @return array<string>
     */
    public function __invoke(): array
    {
        $errors = [];

        $nodesWithNodeImage = $this->getAllNodesWithNodeImages();

        foreach ($nodesWithNodeImage as $nodeId => $nodeImage) {
            if (trim($nodeImage->path) === '') {
                $errors[] = "Node image path for node $nodeId is empty";

                continue;
            }
            if (trim($nodeImage->title) === '') {
                $errors[] = "Node image title for node $nodeId is empty";
            }

            $fullPath = STORIES . "/{$this->game->gameId}/img/$nodeImage->path";
            if (!is_readable($fullPath)) {
                $errors[] = "Node image path `$fullPath` for node $nodeId is not readable";

                continue;
            }

            $info = getimagesize($fullPath);

            if ($info === false || $info['mime'] !== 'image/jpeg') {
                $errors[] = "Node image path `$fullPath` for node $nodeId is not a valid jpeg file";

                continue;
            }

            if ($info[0] !== 960) {
                $errors[] = "Node image path `$fullPath` for node $nodeId is not 960px wide ($info[0]px)";
            }
            if ($info[1] > 960) {
                $errors[] = "Node image path `$fullPath` for node $nodeId is greater than 960px high ($info[1]px)";
            }
        }

        return $errors;
    }

    /**
     * @return array<\App\Story\Nodes\NodeImage>
     */
    public function getAllNodesWithNodeImages(): array
    {
        $nodes = [];

        foreach ($this->game->nodes as $node) {
            if (!isset($node->image)) {
                continue;
            }

            $nodes[$node->id] = $node->image;
        }

        return $nodes;
    }
}
