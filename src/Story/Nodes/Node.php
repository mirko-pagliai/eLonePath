<?php
declare(strict_types=1);

namespace App\Story\Nodes;

use Michelf\Markdown;

/**
 * @phpstan-import-type PassageNodeData from \App\Story\Nodes\PassageNode
 * @phpstan-import-type DiceNodeData from \App\Story\Nodes\DiceNode
 * @phpstan-import-type VictoryNodeData from \App\Story\Nodes\VictoryNode
 * @phpstan-import-type DefeatNodeData from \App\Story\Nodes\DefeatNode
 */
abstract class Node
{
    public protected(set) readonly string $content;

    /**
     * @param array{path: string, title: string}|null $image `path` is the filename only (e.g. `11.jpg`), resolved
     *  by the template against `webroot/assets/stories/{gameId}/img/`.
     */
    public function __construct(
        protected(set) readonly int $id,
        protected readonly string $gameId,
        string $content,
        protected(set) ?array $image,
    ) {
        $this->content = Markdown::defaultTransform($content);
    }

    /**
     * The kind of node this is. Fixed per subclass.
     */
    abstract public function type(): NodeType;

    /**
     * Builds the concrete `Node` subclass matching `$data['type']`.
     *
     * @param array<string, mixed> $data
     */
    public static function createFromArray(int $id, string $gameId, array $data): Node
    {
        $type = NodeType::from($data['type']);

        if ($type === NodeType::PASSAGE) {
            /** @var PassageNodeData $data */
            return PassageNode::createFromArray($id, $gameId, $data);
        }

        if ($type === NodeType::DICE) {
            /** @var DiceNodeData $data */
            return DiceNode::createFromArray($id, $gameId, $data);
        }

        if ($type === NodeType::VICTORY) {
            /** @var VictoryNodeData $data */
            return VictoryNode::createFromArray($id, $gameId, $data);
        }

        /** @var DefeatNodeData $data */
        return DefeatNode::createFromArray($id, $gameId, $data);
    }
}
