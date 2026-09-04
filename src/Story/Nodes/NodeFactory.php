<?php
declare(strict_types=1);

namespace App\Story\Nodes;

use RuntimeException;

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
        $nodeTypeClass = match ($data['type']) {
            'defeat' => DefeatNode::class,
            'dice' => DiceNode::class,
            'passage' => PassageNode::class,
            'victory' => VictoryNode::class,
            default => throw new RuntimeException("Unknown node type: `{$data['type']}`."),
        };

        return $nodeTypeClass::createFromArray($id, $gameId, $data);
    }
}
