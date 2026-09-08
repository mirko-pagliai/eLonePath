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
    public protected(set) readonly string $content;

    public function __construct(
        protected(set) readonly int $id,
        protected readonly string $gameId,
        string $content,
    ) {
        $this->content = preg_replace(
            pattern: '/!\[([^\]]+)\]\(([^)]+)\)/',
            replacement: '![$1](/assets/stories/' . $this->gameId . '/img/$2)',
            subject: $content,
        ) ?: $content;
    }

    /**
     * Every subclass narrows this to its own specific data shape (e.g. `PassageNodeData`) in its own docblock —
     * `Node` itself only knows the generic shape every `Arrayable` promises, not the details of any one subclass.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
