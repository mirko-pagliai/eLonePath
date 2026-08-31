<?php
declare(strict_types=1);

namespace App\Story;

/**
 * @phpstan-type DefeatNodeData array{
 *     content: string,
 *     image: array{path: string, title: string}|null,
 *     type: string,
 * }
 */
class DefeatNode extends Node
{
    public function type(): NodeType
    {
        return NodeType::DEFEAT;
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
            image: $data['image'] ?? null,
        );
    }
}
