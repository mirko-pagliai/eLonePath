<?php
declare(strict_types=1);

namespace App\Story;

class Game
{
    /**
     * @var array<\App\Story\Node>
     */
    public protected(set) array $nodes;

    public function __construct(
        protected(set) readonly string $gameId,
        protected(set) readonly string $title,
        protected(set) readonly string $author,
        protected(set) readonly string $translators,
        protected(set) readonly string $description,
        protected(set) readonly string $language,
        protected(set) readonly string $version,
        array $nodes,
    ) {
        foreach ($nodes as $nodeId => $node) {
//            dd($node);
            $this->nodes[$nodeId] = new Node(
                id: $nodeId,
                gameId: $this->gameId,
                content: $node['content'],
                choices: $node['choices'],
                type: $node['type'],
                victory: $node['victory'],
            );
        }
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

    public static function createFromArray(array $data): Game
    {
        return new self(
            gameId: $data['game']['id'],
            title: $data['game']['title'],
            author: $data['game']['author'],
            translators: $data['game']['translators'],
            description: $data['game']['description'],
            language: $data['game']['language'],
            version: $data['game']['version'],
            nodes: $data['nodes'],
        );
    }
}
