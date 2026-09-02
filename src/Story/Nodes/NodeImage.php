<?php
declare(strict_types=1);

namespace App\Story\Nodes;

use Elone\Core\Contract\Arrayable;

/**
 * An image attached to a node — a cover, an illustration going with a passage, and so on.
 *
 * @phpstan-type NodeImageData array{path: string, title: string}
 */
class NodeImage implements Arrayable
{
    /**
     * @param string $path Filename only (e.g. `11.jpg`) — resolved by the template against
     *  `webroot/assets/stories/{gameId}/img/`.
     * @param string $title
     */
    public function __construct(protected(set) readonly string $path, protected(set) readonly string $title)
    {
    }

    /**
     * @param NodeImageData $data
     */
    public static function createFromArray(array $data): NodeImage
    {
        return new self(path: $data['path'], title: $data['title']);
    }

    /**
     * @return NodeImageData
     */
    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'title' => $this->title,
        ];
    }
}
