<?php
declare(strict_types=1);

namespace App\Story;

use Michelf\Markdown;

/**
 * @phpstan-import-type ChoiceData from \App\Story\Choice
 * @phpstan-type NodeData array{
 *     content: string,
 *     image: array{path: string, title: string}|null,
 *     choices: list<ChoiceData>,
 *     type: string,
 *     victory: bool|null,
 * }
 */
class Node
{
    public protected(set) readonly string $content;

    /**
     * @param list<\App\Story\Choice> $choices
     */
    public function __construct(
        protected(set) readonly int $id,
        protected readonly string $gameId,
        string $content,
        protected(set) ?array $image,
        protected(set) array $choices,
        protected(set) readonly NodeType $type,
        protected(set) readonly ?bool $victory,
    ) {
        $this->content = Markdown::defaultTransform($content);
    }

    /**
     * @param NodeData $data
     */
    public static function createFromArray(int $id, string $gameId, array $data): Node
    {
        return new self(
            id: $id,
            gameId: $gameId,
            content: $data['content'],
            image: $data['image'] ?? null,
            choices: array_map(
                callback: fn(array $choice): Choice => Choice::createFromArray($choice),
                array: $data['choices'],
            ),
            type: NodeType::from($data['type']),
            victory: $data['victory'],
        );
    }
}
