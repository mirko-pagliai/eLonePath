<?php
declare(strict_types=1);

namespace App\Story\Nodes;

/**
 * @phpstan-import-type NodeImageData from \App\Story\Nodes\NodeImage
 * @phpstan-type VictoryNodeData array{
 *     content: string,
 *     image: NodeImageData|null,
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
            'image' => $this->image?->toArray(),
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
            image: isset($data['image']) ? NodeImage::createFromArray($data['image']) : null,
        );
    }
}
