<?php
declare(strict_types=1);

namespace Elone\Debugger;

use App\Story\Game;

/**
 * Validates every node's leading image (see `App\Story\Nodes\Node::extractLeadingImage()`) against the fixed
 * requirements every story image must meet: readable, non-empty, a valid JPEG, exactly 960px wide, at most 960px
 * tall.
 */
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

        $dir = STORIES . "/{$this->game->gameId}/img";

        $nodesWithImages = $this->getAllNodesWithImages();

        foreach ($nodesWithImages as $nodeId => $image) {
            if (trim($image['alt']) === '') {
                $errors[] = "Node image alt text for node $nodeId is empty";
            }

            $fullPath = "$dir/{$image['path']}";
            if (!is_readable($fullPath)) {
                $errors[] = "Node image path `$fullPath` for node $nodeId is not readable";

                continue;
            }

            // Checked explicitly, before getimagesize()
            if (filesize($fullPath) === 0) {
                $errors[] = "Node image path `$fullPath` for node $nodeId is an empty file";

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
     * Every node whose `content` starts with an image, with the image's filename and alt text.
     *
     * @return array<int, array{path: string, alt: string}>
     */
    public function getAllNodesWithImages(): array
    {
        $nodes = [];

        foreach ($this->game->nodes as $node) {
            $result = preg_match(
                pattern: '/!\[([^\]]*)\]\(([^)]+)\)/',
                subject: $node->content,
                matches: $matches,
            );

            if ($result === 0) {
                continue;
            }

            $nodes[$node->id] = [
                'alt' => $matches['1'] ?? '',
                'path' => $matches['2'] ?? '' ? basename($matches['2']) : '',
            ];
        }

        return $nodes;
    }
}
