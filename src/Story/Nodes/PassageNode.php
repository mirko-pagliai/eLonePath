<?php
declare(strict_types=1);

namespace App\Story\Nodes;

/**
 * @phpstan-import-type ChoiceData from \App\Story\Nodes\Choice
 * @phpstan-type PassageNodeData array{
 *     content: string,
 *     choices?: list<ChoiceData>,
 *     type: string,
 * }
 */
class PassageNode extends Node
{
    /**
     * @param list<\App\Story\Nodes\Choice> $choices
     */
    public function __construct(int $id, string $gameId, string $content, protected(set) readonly array $choices)
    {
        parent::__construct($id, $gameId, $content);
    }

    public function getType(): NodeType
    {
        return NodeType::PASSAGE;
    }

    /**
     * @return PassageNodeData
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'type' => $this->getType()->value,
            'choices' => array_map(
                callback: fn(Choice $choice): array => $choice->toArray(),
                array: $this->choices,
            ),
        ];
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
            choices: array_map(
                callback: fn(array $choice): Choice => Choice::createFromArray($choice),
                array: $data['choices'] ?? [],
            ),
        );
    }
}
