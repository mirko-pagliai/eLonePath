<?php
declare(strict_types=1);

namespace App\View\Helper;

use Elone\Core\View\Helper\Helper;

/**
 * A node's `content` may optionally start with a single image, written as ordinary Markdown
 * (`![alt text](filename.jpg)`) — exactly one, at the very beginning, or none at all. This helper pulls that
 * leading image apart from the rest of the content, resolving its filename against
 * `webroot/assets/stories/{gameId}/img/` and rendering it via `Html->image()`, so a template gets back ready-made
 * `<img>` markup for the image (if there was one) plus whatever markdown text follows it, rather than raw pieces
 * it would have to assemble itself.
 *
 * This only recognizes the image when it's the very first thing in `content`; an image Markdown appearing
 * anywhere else is left untouched here — validating that the rule was actually followed is the debugger's job,
 * not this helper's.
 *
 * @property-read \App\View\AppView $view
 */
final class StoryHelper extends Helper
{
    /**
     * The CSS class every story image shares — fixed here, not left to the caller, since story images are always
     * meant to look the same.
     */
    private const string IMAGE_CLASS = 'img-fluid mx-auto mb-5 d-block';

    /**
     * Pulls the leading image out of `$content`, if there is one, and renders it via `Html->image()` — resolving
     * its filename against `webroot/assets/stories/{gameId}/img/` and applying the fixed styling every story
     * image shares.
     *
     * @return array{html: string|null, content: string}
     */
    public function image(string $content, string $gameId): array
    {
        if (!preg_match('/^!\[([^\]]*)\]\(([^)]+)\)\s*/', $content, $matches)) {
            return [
                'html' => null,
                'content' => $content,
            ];
        }

        $html = $this->view->Html->image(
            path: "/assets/stories/$gameId/img/{$matches[2]}",
            options: ['alt' => $matches[1], 'class' => self::IMAGE_CLASS],
        );

        return [
            'html' => $html,
            'content' => substr($content, strlen($matches[0])),
        ];
    }
}
