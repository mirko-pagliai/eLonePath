<?php
declare(strict_types=1);

namespace App\Story;

use Michelf\Markdown;

class Node
{
    /**
     * @var array<\App\Story\Choice>
     */
    public protected(set) array $choices;

    /**
     * Image related to `webroot/assets/img/stories` if it exists for this node, otherwise `null`.
     *
     * @var string|null
     */
    public protected(set) ?string $image = null;

    public protected(set) readonly string $text;

    public function __construct(
        protected(set) readonly int $id,
        protected readonly string $gameId,
        string $content,
        array $choices,
        protected readonly string $type = 'story',
    ) {
        $this->text = Markdown::defaultTransform($content);

        if (file_exists("webroot/assets/img/stories/$this->gameId/$this->id.jpg")) {
            $this->image = "/assets/img/stories/$this->gameId/$this->id.jpg";
        }

        $this->choices = [];
        foreach ($choices as $choice) {
            $this->choices[] = new Choice(
                text: $choice->text,
                target: (int)$choice->target,
            );
        }
    }
}
