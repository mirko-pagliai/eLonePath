<?php
declare(strict_types=1);

namespace Elone\Debugger;

use App\Story\Game;

/**
 * Validates the image a node's content may reference (see `App\Story\Nodes\Node::findImages()`) — at most one,
 * at the very beginning — against the fixed requirements every story image must meet: readable, non-empty, a
 * valid JPEG, exactly 960px wide, at most 960px tall, and with non-empty alt text.
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

        foreach ($this->getAllNodesWithImages() as $nodeId => $images) {
            $counter = count($images);
            if ($counter > 1) {
                $errors[] = "Node $nodeId has $counter  images; only one, at the very beginning, is allowed";
            }

            $content = $this->game->getNode($nodeId)->content;
            if (!str_starts_with(ltrim($content), '![')) {
                $errors[] = "Node $nodeId has an image, but not at the very beginning of its content";
            }

            $image = $images[0];
            $filename = basename($image['path']);

            if (trim($image['alt']) === '') {
                $errors[] = "Node image alt text for node $nodeId (`$filename`) is empty";
            }

            $fullPath = "$dir/$filename";
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
     * Every node with at least one image, each with its own list of images (in the order they appear in that
     * node's content) — a node can reference more than one.
     *
     * @return array<int, list<array{alt: string, path: string}>>
     */
    public function getAllNodesWithImages(): array
    {
        $nodes = [];

        foreach ($this->game->nodes as $node) {
            $images = $node->findImages();
            if ($images !== []) {
                $nodes[$node->id] = $images;
            }
        }

        return $nodes;
    }
}
