<?php
declare(strict_types=1);

namespace App\Story;

use RuntimeException;

/**
 * @phpstan-import-type PassageNodeData from \App\Story\PassageNode
 * @phpstan-import-type DiceNodeData from \App\Story\DiceNode
 * @phpstan-import-type VictoryNodeData from \App\Story\VictoryNode
 * @phpstan-import-type DefeatNodeData from \App\Story\DefeatNode
 * @phpstan-type GameData array{
 *     game: array{
 *         id: string,
 *         title: string,
 *         author: string,
 *         translators: string,
 *         description: string,
 *         language: string,
 *         version: string,
 *     },
 *     nodes: array<int, PassageNodeData|DiceNodeData|VictoryNodeData|DefeatNodeData>,
 * }
 */
class Game
{
    /**
     * @param array<int, \App\Story\Node> $nodes
     */
    public function __construct(
        protected(set) readonly string $gameId,
        protected(set) readonly string $title,
        protected(set) readonly string $author,
        protected(set) readonly string $translators,
        protected(set) readonly string $description,
        protected(set) readonly string $language,
        protected(set) readonly string $version,
        protected(set) array $nodes,
    ) {
    }

    /**
     * Retrieves a node by its unique identifier.
     *
     * @param int $nodeId The unique identifier of the node to retrieve.
     * @return \App\Story\Node The node associated with the given identifier.
     */
    public function getNode(int $nodeId): Node
    {
        return $this->nodes[$nodeId];
    }

    /**
     * @param GameData $data
     */
    public static function createFromArray(array $data): Game
    {
        $nodes = [];
        foreach ($data['nodes'] as $nodeId => $node) {
            $nodes[$nodeId] = Node::createFromArray(id: $nodeId, gameId: $data['game']['id'], data: $node);
        }

        return new self(
            gameId: $data['game']['id'],
            title: $data['game']['title'],
            author: $data['game']['author'],
            translators: $data['game']['translators'],
            description: $data['game']['description'],
            language: $data['game']['language'],
            version: $data['game']['version'],
            nodes: $nodes,
        );
    }

    public static function createFromFile(string $path): Game
    {
        if (!is_readable($path)) {
            throw new RuntimeException("Failed to read `$path`.");
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("Failed to read `$path`.");
        }

        /** @var GameData|null $json */
        $json = json_decode($contents, true);
        if (!is_array($json)) {
            throw new RuntimeException("Failed to parse `$path`.");
        }

        return self::createFromArray($json);
    }
}
