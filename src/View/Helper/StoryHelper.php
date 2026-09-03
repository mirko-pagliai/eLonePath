<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Story\Nodes\Node;
use Elone\Core\View\Helper\Helper;

/**
 * A node's `content` may optionally start with a single image — see `App\Story\Nodes\Node::extractLeadingImage()`
 * for the exact rule. This helper pulls that leading image apart from the rest of the content, resolving its
 * filename against `webroot/assets/stories/{gameId}/img/` and rendering it via `Html->image()`, so a template
 * gets back ready-made `<img>` markup for the image (if there was one) plus whatever markdown text follows it,
 * rather than raw pieces it would have to assemble itself.
 *
 * @property-read \Elone\Core\View\Helper\HtmlHelper $Html
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
     * @param string $content The node's raw content.
     * @param string $gameId The game's identifier, used to resolve the image's filename into a full asset path.
     * @return array{html: string|null, content: string}
     */
    public function image(string $content, string $gameId): array
    {
        $extracted = Node::extractLeadingImage($content);

        if ($extracted['path'] === null) {
            return [
                'html' => null,
                'content' => $extracted['content'],
            ];
        }

        $html = $this->Html->image(
            path: "/assets/stories/$gameId/img/{$extracted['path']}",
            options: [
                'alt' => $extracted['alt'] ?? '',
                'class' => self::IMAGE_CLASS,
            ],
        );

        return [
            'html' => $html,
            'content' => $extracted['content'],
        ];
    }
}
