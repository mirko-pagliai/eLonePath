<?php
declare(strict_types=1);

namespace App\Story\Nodes;

use Elone\Core\Contract\Arrayable;

/**
 * A single node — a "page" — in a game's story graph: some content, and either a way to continue (a passage's
 * choices, a dice check's two outcomes) or an ending (victory, defeat). Every concrete node type extends this.
 *
 * `content` may reference at most one image, at the very beginning, written as ordinary Markdown
 * (`![alt text](filename.jpg)`) — `Elone\Debugger\NodeImagesWalker` is what actually enforces that rule; the
 * constructor here only rewrites whatever image it finds (anywhere, however many) to resolve under this game's
 * own asset directory, so `content` is ready to render as-is, with no separate image-extraction step.
 *
 * Built from raw story data via `NodeFactory::createFromArray()`, not through `Node` itself — `Node` only knows
 * the shape every node shares, not which concrete types exist or how to choose between them.
 */
abstract class Node implements Arrayable
{
    protected const string IMAGE_PATTERN = '/!\[([^\]]*)\]\(([^)]+)\)/';

    public protected(set) readonly string $content;

    public function __construct(
        protected(set) readonly int $id,
        protected readonly string $gameId,
        string $content,
    ) {
        $this->content = self::resolveImagePaths(content: $content, gameId: $gameId);
    }

    /**
     * `$markdown` with every image's path rewritten to resolve under `$gameId`'s own asset directory —
     * `/assets/stories/{gameId}/img/{path}` — everything else (alt text, surrounding text) untouched.
     */
    public static function resolveImagePaths(string $content, string $gameId): string
    {
        return preg_replace(
            pattern: self::IMAGE_PATTERN,
            replacement: "![\$1](/assets/stories/$gameId/img/\$2)",
            subject: $content,
        ) ?: $content;
    }

    /**
     * Every image referenced in `$markdown`, in the order they appear. Alt text may be empty (`![](path.jpg)`) —
     * the pattern still matches it, deliberately: `NodeImagesWalker` relies on finding such images to flag them
     * as missing alt text, rather than never seeing them at all.
     *
     * @return list<array{alt: string, path: string}>
     */
    public function findImages(): array
    {
        preg_match_all(
            pattern: $this::IMAGE_PATTERN,
            subject: $this->content,
            matches: $matches,
            flags: PREG_SET_ORDER,
        );

        return array_map(
            callback: fn(array $match): array => ['alt' => $match[1], 'path' => $match[2]],
            array: $matches,
        );
    }

    /**
     * Every subclass narrows this to its own specific data shape (e.g. `PassageNodeData`) in its own docblock —
     * `Node` itself only knows the generic shape every `Arrayable` promises, not the details of any one subclass.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
