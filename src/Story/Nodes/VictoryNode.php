<?php
declare(strict_types=1);

namespace App\Story\Nodes;

/**
 * @phpstan-type VictoryNodeData array{
 *     content: string,
 *     type: string,
 * }
 */
class VictoryNode extends Node
{
    public function getType(): NodeType
    {
        return NodeType::VICTORY;
    }

    /**
     * @return VictoryNodeData
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'type' => $this->getType()->value,
        ];
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
        );
    }
}
