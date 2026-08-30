<?php
declare(strict_types=1);

namespace App\Story;

use Michelf\Markdown;

/**
 * @phpstan-import-type ChoiceData from \App\Story\Choice
 * @phpstan-type NodeData array{
 *     content: string,
 *     choices: list<ChoiceData>,
 *     type: string,
 *     victory: bool|null,
 * }
 */
class Node
{
    /**
     * Image related to `webroot/assets/img/stories` if it exists for this node, otherwise `null`.
     */
    public protected(set) ?string $image = null;

    public protected(set) readonly string $content;

    /**
     * @param list<\App\Story\Choice> $choices
     */
    public function __construct(
        protected(set) readonly int $id,
        protected readonly string $gameId,
        string $content,
        protected(set) array $choices,
        protected(set) readonly NodeType $type,
        protected(set) readonly ?bool $victory,
    ) {
        $this->content = Markdown::defaultTransform($content);

        if (file_exists("webroot/assets/img/stories/$gameId/$id.jpg")) {
            $this->image = "/assets/img/stories/$gameId/$id.jpg";
        }
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
            choices: array_map(
                callback: fn(array $choice): Choice => Choice::createFromArray($choice),
                array: $data['choices'],
            ),
            type: NodeType::from($data['type']),
            victory: $data['victory'],
        );
    }
}
