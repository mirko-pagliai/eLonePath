<?php
declare(strict_types=1);

namespace App\Story\Nodes;

/**
 * @phpstan-type VictoryNodeData array{
 *     content: string,
 *     image: array{path: string, title: string}|null,
 *     type: string,
 * }
 */
class VictoryNode extends Node
{
    public function type(): NodeType
    {
        return NodeType::VICTORY;
    }

    /**
     * @param VictoryNodeData $data
     */
    public static function createFromArray(int $id, string $gameId, array $data): VictoryNode
    {
        return new self(
            id: $id,
            gameId: $gameId,
            content: $data['content'],
            image: $data['image'] ?? null,
        );
    }
}
