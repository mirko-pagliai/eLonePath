<?php
declare(strict_types=1);

namespace App\Story;

/**
 * Represents a selectable choice with an associated target.
 *
 * The `Choice` class replaces placeholders within the provided text with a specified target string upon instantiation.
 */
class Choice
{
    public protected(set) readonly string $content;

    public function __construct(string $content, protected(set) readonly int $target)
    {
        $this->content = str_replace(
            search: '{{page}}',
            replace: (string)$target,
            subject: $content,
        );
    }

    public static function createFromArray(array $data): Choice
    {
        return new self(
            content: $data['content'],
            target: $data['target'],
        );
    }
}
