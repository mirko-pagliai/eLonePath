<?php
declare(strict_types=1);

namespace App\Story\Nodes;

/**
 * @phpstan-import-type PassageNodeData from \App\Story\Nodes\PassageNode
 * @phpstan-import-type DiceNodeData from \App\Story\Nodes\DiceNode
 * @phpstan-import-type VictoryNodeData from \App\Story\Nodes\VictoryNode
 * @phpstan-import-type DefeatNodeData from \App\Story\Nodes\DefeatNode
 */
abstract class Node
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
}
