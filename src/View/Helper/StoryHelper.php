<?php
declare(strict_types=1);

namespace App\View\Helper;

use Elone\Core\View\Helper\Helper;

/**
 * A node's `content` may optionally start with a single image, written as ordinary Markdown
 * (`![alt text](filename.jpg)`) — exactly one, at the very beginning, or none at all. This helper pulls that
 * leading image apart from the rest of the content, resolving its filename against
 * `webroot/assets/stories/{gameId}/img/`, so a template can render it separately from the markdown-rendered text
 * that follows it — a styled image above the passage, not inline within it.
 *
 * This only recognizes the image when it's the very first thing in `content`; an image Markdown appearing
 * anywhere else is left untouched here — validating that the rule was actually followed is the debugger's job,
 * not this helper's.
 *
 * Takes the `View` itself, not any specific other helper, in its constructor — the same way a template does. Any
 * other helper this one needs (e.g. `$this->view->Html`) is reached lazily, inside a method body, not at
 * construction time: by the time any of this helper's own methods actually run, every helper `AppView` loads is
 * already registered, regardless of which order they were loaded in — so this composes safely even if helpers end
 * up referencing each other in a cycle, which a constructor directly depending on another helper's instance
 * could not.
 */
final class StoryHelper extends Helper
{
    /**
     * Pulls the leading image out of `$content`, if there is one, resolving its filename against
     * `webroot/assets/stories/{gameId}/img/`.
     *
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
