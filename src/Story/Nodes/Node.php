<?php
declare(strict_types=1);

namespace App\Story\Nodes;

use Elone\Core\Contract\Arrayable;

/**
 * A single node — a "page" — in a game's story graph: some content, and either a way to continue (a passage's
 * choices, a dice check's two outcomes) or an ending (victory, defeat). Every concrete node type extends this.
 *
 * `content` may optionally start with a single image, written as ordinary Markdown (`![alt text](filename.jpg)`)
 * — exactly one, at the very beginning, or none at all; `extractLeadingImage()` is the one place that pattern is
 * parsed, shared by `StoryHelper` (rendering) and the debugger's `NodeImagesWalker` (validation), so neither one
 * risks drifting from the other's idea of what counts as a node's image.
 *
 * Built from raw story data via `NodeFactory::createFromArray()`, not through `Node` itself — `Node` only knows
 * the shape every node shares, not which concrete types exist or how to choose between them.
 */
abstract class Node implements Arrayable
{
    public function __construct(
        protected(set) readonly int $id,
        protected readonly string $gameId,
        protected(set) readonly string $content,
    ) {
    }

    /**
     * Every subclass narrows this to its own specific data shape (e.g. `PassageNodeData`) in its own docblock —
     * `Node` itself only knows the generic shape every `Arrayable` promises, not the details of any one subclass.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;

    /**
     * Splits `$content` into its leading image (if any) and the markdown that follows it. This only recognizes
     * the image when it's the very first thing in `$content`; an image Markdown appearing anywhere else is left
     * untouched — the leading-only rule is enforced by never looking past the start, not by validating the rest
     * of the string.
     *
     * @param string $content A node's raw content, as stored in the story data.
     * @return array{path: string|null, alt: string|null, content: string} `path` is the image's filename (not a
     *  full path — resolving it against `webroot/assets/stories/{gameId}/img/` is the caller's job) and `alt` its
     *  alt text, both `null` when `$content` doesn't start with an image. `content` is always what remains after
     *  the leading image (if any) and the blank lines right after it are stripped.
     */
    public static function extractLeadingImage(string $content): array
    {
        if (!preg_match('/^!\[([^\]]*)\]\(([^)]+)\)\s*/', $content, $matches)) {
            return [
                'path' => null,
                'alt' => null,
                'content' => $content,
            ];
        }

        return [
            'path' => $matches[2],
            'alt' => $matches[1],
            'content' => substr($content, strlen($matches[0])),
        ];
    }
}
