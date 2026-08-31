<?php
declare(strict_types=1);

namespace App\Story;

use App\Story\Nodes\Node;
use Elone\Core\Exception\HttpException;
use JsonException;
use RuntimeException;

/**
 * @phpstan-import-type PassageNodeData from \App\Story\Nodes\PassageNode
 * @phpstan-import-type DiceNodeData from \App\Story\Nodes\DiceNode
 * @phpstan-import-type VictoryNodeData from \App\Story\Nodes\VictoryNode
 * @phpstan-import-type DefeatNodeData from \App\Story\Nodes\DefeatNode
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
     * @param array<int, \App\Story\Nodes\Node> $nodes
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
     * @return \App\Story\Nodes\Node The node associated with the given identifier.
     * @throws \Elone\Core\Exception\HttpException If no node with that identifier exists in this game.
     */
    public function getNode(int $nodeId): Node
    {
        if (!isset($this->nodes[$nodeId])) {
            throw new HttpException("Node `$nodeId` not found in `$this->gameId`.", statusCode: 404);
        }

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

    /**
     * Creates a `Game` instance from a JSON file.
     *
     * @param string $path The file path to the JSON file.
     * @return \App\Story\Game A Game instance created from the JSON data.
     * @throws \RuntimeException If the file is not readable, the contents cannot be read, or the JSON cannot be parsed
     * or does not match the expected structure.
     */
    public static function createFromFile(string $path): Game
    {
        if (!is_readable($path)) {
            throw new RuntimeException("Failed to read `$path`.");
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("Failed to read `$path`.");
        }

        try {
            $json = json_decode($contents, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(
                "Failed to parse `$path`: {$exception->getMessage()}.",
                previous: $exception,
            );
        }

        if (!is_array($json)) {
            throw new RuntimeException("Failed to parse `$path`: expected a JSON object at the top level.");
        }

        /** @var GameData $json */
        return self::createFromArray($json);
    }
}
