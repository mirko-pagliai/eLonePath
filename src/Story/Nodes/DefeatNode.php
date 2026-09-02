<?php
declare(strict_types=1);

namespace App\Story\Nodes;

/**
 * @phpstan-import-type NodeImageData from \App\Story\Nodes\NodeImage
 * @phpstan-type DefeatNodeData array{
 *     content: string,
 *     image: NodeImageData|null,
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
        return $this->baseArray();
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
            image: isset($data['image']) ? NodeImage::createFromArray($data['image']) : null,
        );
    }
}
