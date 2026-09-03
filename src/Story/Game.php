<?php
declare(strict_types=1);

namespace App\Story;

use App\Story\Nodes\Node;
use App\Story\Nodes\NodeFactory;
use Elone\Core\Contract\Arrayable;
use Elone\Core\Exception\HttpException;
use JsonException;
use RuntimeException;

/**
 * @phpstan-import-type NodeData from \App\Story\Nodes\NodeFactory
 * @phpstan-type GameHeaderData array{
 *     id: string,
 *     title: string,
 *     author: string,
 *     translators?: string,
 *     description: string,
 *     language: string,
 *     version: string,
 *     preface?: string,
 *     requires_combat?: bool,
 * }
 * @phpstan-type GameData array{
 *     game: GameHeaderData,
 *     nodes: array<int, NodeData>,
 * }
 */
class Game implements Arrayable
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
        protected(set) readonly string $preface,
        protected(set) readonly bool $requiresCombat,
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
     * Exports this game — its own metadata plus every node — as a plain array; the reverse of `createFromArray()`.
     *
     * The `game` portion reuses `GameHeaderData`, the same shape `createFromArray()` reads. The `nodes` portion
     * can't reuse `GameData`'s own `nodes` type the same way: each entry comes from that node's own `toArray()`,
     * which — per `Node`'s own contract — only promises the generic `array<string, mixed>`, not the specific shape
     * for its concrete type. That precision loss is a direct, accepted consequence of `Node` not knowing about its
     * subclasses; `Game` can't promise more than what `Node` itself promises.
     *
     * @return array{game: GameHeaderData, nodes: array<int, array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'game' => [
                'id' => $this->gameId,
                'title' => $this->title,
                'author' => $this->author,
                'translators' => $this->translators,
                'description' => $this->description,
                'language' => $this->language,
                'version' => $this->version,
                'preface' => $this->preface,
                'requires_combat' => $this->requiresCombat,
            ],
            'nodes' => array_map(
                callback: fn(Node $node): array => $node->toArray(),
                array: $this->nodes,
            ),
        ];
    }

    /**
     * @param GameData $data
     */
    public static function createFromArray(array $data): Game
    {
        $nodes = [];
        foreach ($data['nodes'] as $nodeId => $node) {
            $nodes[$nodeId] = NodeFactory::createFromArray(id: $nodeId, gameId: $data['game']['id'], data: $node);
        }

        return new self(
            gameId: $data['game']['id'],
            title: $data['game']['title'],
            author: $data['game']['author'],
            translators: $data['game']['translators'] ?? '',
            description: $data['game']['description'],
            language: $data['game']['language'],
            version: $data['game']['version'],
            preface: $data['game']['preface'] ?? '',
            // Defaults to false: a story that doesn't declare this key is treated as pure narration, needing no
            // character before it starts — the same way every story already worked before this key existed.
            requiresCombat: $data['game']['requires_combat'] ?? false,
            nodes: $nodes,
        );
    }

    /**
     * Creates a `Game` instance from a JSON string.
     *
     * @param string $json The JSON string.
     * @return \App\Story\Game A Game instance created from the JSON data.
     * @throws \RuntimeException If the JSON cannot be parsed or does not match the expected structure.
     */
    public static function createFromString(string $json): Game
    {
        try {
            $data = self::decode($json);
        } catch (RuntimeException $exception) {
            throw new RuntimeException("Failed to parse JSON: {$exception->getMessage()}.", previous: $exception);
        }

        return self::createFromArray($data);
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

        // Checks if the file is a `story.json` file
        if (!str_ends_with($path, 'story.json')) {
            throw new RuntimeException("Expected `$path` to be a `story.json` file.");
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("Failed to read `$path`.");
        }

        try {
            $data = self::decode($contents);
        } catch (RuntimeException $exception) {
            throw new RuntimeException("Failed to parse `$path`: {$exception->getMessage()}.", previous: $exception);
        }

        return self::createFromArray($data);
    }

    /**
     * Decodes a JSON string and validates it has the shape `createFromArray()` expects. The message on the
     * exception it throws is the bare reason only, without a trailing period — `createFromString()` and
     * `createFromFile()` each wrap it with their own context (a file path, or none) and add the period themselves.
     *
     * @return GameData
     * @throws \RuntimeException If the JSON cannot be parsed or does not match the expected structure.
     */
    private static function decode(string $json): array
    {
        try {
            $data = json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException($exception->getMessage(), previous: $exception);
        }

        if (!is_array($data)) {
            throw new RuntimeException('expected a JSON object at the top level');
        }

        /** @var GameData $data */
        return $data;
    }
}
