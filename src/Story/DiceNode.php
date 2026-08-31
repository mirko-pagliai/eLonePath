<?php
declare(strict_types=1);

namespace App\Story;

/**
 * @phpstan-type DiceNodeData array{
 *     content: string,
 *     image: array{path: string, title: string}|null,
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
        ?array $image,
        protected(set) readonly int $requiredRolls,
        protected(set) readonly int $minimum,
        protected(set) readonly int $targetSuccess,
        protected(set) readonly int $targetFailure,
    ) {
        parent::__construct($id, $gameId, $content, $image);
    }

    public function type(): NodeType
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
     * @param DiceNodeData $data
     */
    public static function createFromArray(int $id, string $gameId, array $data): DiceNode
    {
        return new self(
            id: $id,
            gameId: $gameId,
            content: $data['content'],
            image: $data['image'] ?? null,
            requiredRolls: $data['dice']['required_rolls'],
            minimum: $data['dice']['minimum'],
            targetSuccess: $data['dice']['target_success'],
            targetFailure: $data['dice']['target_failure'],
        );
    }
}
