<?php
declare(strict_types=1);

namespace App\Story\Nodes;

/**
 * @phpstan-import-type ChoiceData from \App\Story\Nodes\Choice
 * @phpstan-import-type NodeImageData from \App\Story\Nodes\NodeImage
 * @phpstan-type PassageNodeData array{
 *     content: string,
 *     image: NodeImageData|null,
 *     choices?: list<ChoiceData>,
 *     type: string,
 * }
 */
class PassageNode extends Node
{
    /**
     * @param list<\App\Story\Nodes\Choice> $choices
     */
    public function __construct(
        int $id,
        string $gameId,
        string $content,
        ?NodeImage $image,
        protected(set) readonly array $choices,
    ) {
        parent::__construct($id, $gameId, $content, $image);
    }

    public function getType(): NodeType
    {
        return NodeType::PASSAGE;
    }

    /**
     * @param PassageNodeData $data
     */
    public static function createFromArray(int $id, string $gameId, array $data): PassageNode
    {
        return new self(
            id: $id,
            gameId: $gameId,
            content: $data['content'],
            image: isset($data['image']) ? NodeImage::createFromArray($data['image']) : null,
            choices: array_map(
                callback: fn(array $choice): Choice => Choice::createFromArray($choice),
                array: $data['choices'] ?? [],
            ),
        );
    }
}
