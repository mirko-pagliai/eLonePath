<?php
declare(strict_types=1);

namespace App\Story\Nodes;

/**
 * @phpstan-type ChoiceData array{content: string, target: int}
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

    /**
     * @param ChoiceData $data
     */
    public static function createFromArray(array $data): Choice
    {
        return new self(
            content: $data['content'],
            target: $data['target'],
        );
    }
}
