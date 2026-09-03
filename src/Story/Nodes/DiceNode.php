<?php
declare(strict_types=1);

namespace App\Story\Nodes;

/**
 * @phpstan-import-type NodeImageData from \App\Story\Nodes\NodeImage
 * @phpstan-type DiceNodeData array{
 *     content: string,
 *     image: NodeImageData|null,
 *     type: string,
 *     dice: array{
 *         required_rolls: int,
 *         minimum: int,
 *         target_success: int,
 *         target_failure: int,
 *     },
 * }
 */
class DiceNode extends Node
{
    public function __construct(
        int $id,
        string $gameId,
        string $content,
        ?NodeImage $image,
        protected(set) readonly int $requiredRolls,
        protected(set) readonly int $minimum,
        protected(set) readonly int $targetSuccess,
        protected(set) readonly int $targetFailure,
    ) {
        parent::__construct($id, $gameId, $content, $image);
    }

    public function getType(): NodeType
    {
        return NodeType::DICE;
    }

    /**
     * Whether the sum of the rolls meets `$minimum`.
     */
    public function isSuccess(int $total): bool
    {
        return $total >= $this->minimum;
    }

    /**
     * The node to go to given the sum of the rolls: `$targetSuccess` if `isSuccess()`, `$targetFailure` otherwise.
     */
    public function targetFor(int $total): int
    {
        return $this->isSuccess($total) ? $this->targetSuccess : $this->targetFailure;
    }

    /**
     * @return DiceNodeData
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'image' => $this->image?->toArray(),
            'type' => $this->getType()->value,
            'dice' => [
                'required_rolls' => $this->requiredRolls,
                'minimum' => $this->minimum,
                'target_success' => $this->targetSuccess,
                'target_failure' => $this->targetFailure,
            ],
        ];
    }

    /**
     * @param DiceNodeData $data
     */
    public static function createFromArray(int $id, string $gameId, array $data): DiceNode
    {
        return new self(
            id: $id,
            gameId: $gameId,
            content: $data['content'],
            image: isset($data['image']) ? NodeImage::createFromArray($data['image']) : null,
            requiredRolls: $data['dice']['required_rolls'],
            minimum: $data['dice']['minimum'],
            targetSuccess: $data['dice']['target_success'],
            targetFailure: $data['dice']['target_failure'],
        );
    }
}
