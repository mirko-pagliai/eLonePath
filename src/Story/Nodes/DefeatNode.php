<?php
declare(strict_types=1);

namespace App\Story\Nodes;

/**
 * @phpstan-type DefeatNodeData array{
 *     content: string,
 *     type: string,
 * }
 */
class DefeatNode extends Node
{
    public function getType(): NodeType
    {
        return NodeType::DEFEAT;
    }

    /**
     * @return DefeatNodeData
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'type' => $this->getType()->value,
        ];
    }

    /**
     * @param DefeatNodeData $data
     */
    public static function createFromArray(int $id, string $gameId, array $data): DefeatNode
    {
        return new self(
            id: $id,
            gameId: $gameId,
            content: $data['content'],
        );
    }
}
