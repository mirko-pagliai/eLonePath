<?php
declare(strict_types=1);

namespace App\Story\Nodes;

use Elone\Core\Contract\Arrayable;

/**
 * @phpstan-import-type PassageNodeData from \App\Story\Nodes\PassageNode
 * @phpstan-import-type DiceNodeData from \App\Story\Nodes\DiceNode
 * @phpstan-import-type VictoryNodeData from \App\Story\Nodes\VictoryNode
 * @phpstan-import-type DefeatNodeData from \App\Story\Nodes\DefeatNode
 * @phpstan-import-type NodeImageData from \App\Story\Nodes\NodeImage
 */
abstract class Node implements Arrayable
{
    public function __construct(
        protected(set) readonly int $id,
        protected readonly string $gameId,
        protected(set) readonly string $content,
        protected(set) ?NodeImage $image,
    ) {
    }

    /**
     * The kind of node this is. Fixed per subclass.
     */
    abstract public function getType(): NodeType;

    /**
     * @return PassageNodeData|DiceNodeData|VictoryNodeData|DefeatNodeData
     */
    abstract public function toArray(): array;

    /**
     * Builds the concrete `Node` subclass matching `$data['type']`.
     *
     * @param PassageNodeData|DiceNodeData|VictoryNodeData|DefeatNodeData $data
     */
    public static function createFromArray(int $id, string $gameId, array $data): Node
    {
        $type = NodeType::from($data['type']);

        if ($type === NodeType::PASSAGE) {
            /** @var PassageNodeData $data */
            return PassageNode::createFromArray($id, $gameId, $data);
        }

        if ($type === NodeType::DICE) {
            /** @var DiceNodeData $data */
            return DiceNode::createFromArray($id, $gameId, $data);
        }

        if ($type === NodeType::VICTORY) {
            /** @var VictoryNodeData $data */
            return VictoryNode::createFromArray($id, $gameId, $data);
        }

        /** @var DefeatNodeData $data */
        return DefeatNode::createFromArray($id, $gameId, $data);
    }

    /**
     * The portion of the array representation shared by every node type — `content`, `image`, and `type` (derived
     * from `getType()`). Each subclass's `toArray()` merges this with whatever else it adds.
     *
     * @return array{content: string, image: NodeImageData|null, type: string}
     */
    protected function baseArray(): array
    {
        return [
            'content' => $this->content,
            'image' => $this->image?->toArray(),
            'type' => $this->getType()->value,
        ];
    }
}
