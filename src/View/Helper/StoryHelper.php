<?php
declare(strict_types=1);

namespace App\View\Helper;

/**
 * A node's `content` may optionally start with a single image, written as ordinary markdown
 * (`![alt text](filename.jpg)`) — exactly one, at the very beginning, or none at all. This helper pulls that leading
 * image apart from the rest of the content, resolving its filename against `webroot/assets/stories/{gameId}/img/`, so
 * a template can render it separately from the markdown-rendered text that follows it — a styled image above the
 * passage, not inline within it.
 *
 * This only recognizes the image when it's the very first thing in `content`; an image markdown appearing anywhere
 * else is left untouched here — validating that the rule was actually followed is the debugger's job, not this
 * helper's.
 */
final class StoryHelper
{
    /**
     * @return array{path: string|null, alt: string|null, content: string}
     */
    public function image(string $content, string $gameId): array
    {
        if (!preg_match('/^!\[([^\]]*)\]\(([^)]+)\)\s*/', $content, $matches)) {
            return [
                'path' => null,
                'alt' => null,
                'content' => $content,
            ];
        }

        return [
            'path' => "/assets/stories/$gameId/img/{$matches[2]}",
            'alt' => $matches[1],
            'content' => substr($content, strlen($matches[0])),
        ];
    }
}
