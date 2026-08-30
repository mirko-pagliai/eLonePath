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
    public protected(set) readonly string $text;

    public function __construct(string $text, protected(set) readonly int $target)
    {
        $this->text = str_replace(
            search: '{{page}}',
            replace: (string)$target,
            subject: $text,
        );
    }
}
