<?php
declare(strict_types=1);

namespace App\Story\Nodes;

/**
 * Builds the concrete `Node` subclass matching a node's `type` field — the one place in the codebase that needs
 * to know about every node type there is. `Node` itself deliberately doesn't: it only knows the shape every node
 * shares, not which concrete types exist. Adding a new node type only ever means updating this class.
 *
 * @phpstan-import-type PassageNodeData from \App\Story\Nodes\PassageNode
 * @phpstan-import-type DiceNodeData from \App\Story\Nodes\DiceNode
 * @phpstan-import-type VictoryNodeData from \App\Story\Nodes\VictoryNode
 * @phpstan-import-type DefeatNodeData from \App\Story\Nodes\DefeatNode
 * @phpstan-type NodeData PassageNodeData|DiceNodeData|VictoryNodeData|DefeatNodeData
 */
final readonly class NodeFactory
{
    /**
     * @param NodeData $data
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
