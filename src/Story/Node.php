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
        protected readonly Game $game,
        string $content,
        protected(set) array $choices,
        protected(set) readonly string $type,
        protected(set) readonly ?bool $victory,
    ) {
        $this->content = Markdown::defaultTransform($content);

        if (file_exists("webroot/assets/img/stories/$game->gameId/$id.jpg")) {
            $this->image = "/assets/img/stories/$game->gameId/$id.jpg";
        }
    }

    /**
     * @param NodeData $data
     */
    public static function createFromArray(int $id, Game $game, array $data): Node
    {
        return new self(
            id: $id,
            game: $game,
            content: $data['content'],
            choices: array_map(
                callback: fn (array $choice): Choice => Choice::createFromArray($choice),
                array: $data['choices'],
            ),
            type: $data['type'],
            victory: $data['victory'],
        );
    }
}
