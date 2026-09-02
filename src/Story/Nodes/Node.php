<?php
declare(strict_types=1);

namespace App\Story\Nodes;

use Elone\Core\Contract\Arrayable;

/**
 * A single node — a "page" — in a game's story graph: some content, an optional illustration, and either a way to
 * continue (a passage's choices, a dice check's two outcomes) or an ending (victory, defeat). Every concrete node
 * type extends this.
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
        protected(set) ?NodeImage $image,
    ) {
    }

    /**
     * The kind of node this is. Fixed per subclass.
     */
    abstract public function getType(): NodeType;

    /**
     * Every subclass narrows this to its own specific data shape (e.g. `PassageNodeData`) in its own docblock —
     * `Node` itself only knows the generic shape every `Arrayable` promises, not the details of any one subclass.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
