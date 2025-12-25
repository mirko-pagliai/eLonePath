<?php
declare(strict_types=1);

namespace eLonePath\Story\DTO;

use InvalidArgumentException;

/**
 * Data Transfer Object for paragraph choices from JSON.
 *
 * Immutable object representing choice data parsed from story files.
 */
readonly class ChoiceDTO
{
    /**
     * Create a choice DTO.
     *
     * @param string $text The text associated with the choice. Cannot be empty.
     * @param int $target The ID of the target paragraph. Must be a positive integer.
     * @param \eLonePath\Story\DTO\ConditionDTO|null $condition An optional condition associated with the choice.
     * @return void
     * @throws \InvalidArgumentException If the provided text is empty or the target ID is not positive.
     */
    public function __construct(
        public string $text,
        public int $target,
        public ?ConditionDTO $condition = null,
    ) {
        if (trim($text) === '') {
            throw new InvalidArgumentException('Choice text cannot be empty');
        }
        if ($target < 1) {
            throw new InvalidArgumentException('Target paragraph ID must be positive');
        }
    }

    /**
     * Create ChoiceDTO from array data.
     *
     * @param array{
     *     text: string,
     *     target: int,
     *     condition?: array{type: string, item?: string, value?: int}
     * } $data Choice data from JSON
     * @return self
     * @throws \InvalidArgumentException If required fields are missing or invalid
     */
    public static function fromArray(array $data): self
    {
        if (empty($data['text'])) {
            throw new InvalidArgumentException('Choice missing "text".');
        }
        if (empty($data['target'])) {
            throw new InvalidArgumentException('Choice missing "target".');
        }

        $condition = null;
        if (isset($data['condition'])) {
            $condition = ConditionDTO::fromArray($data['condition']);
        }

        return new self(text: $data['text'], target: $data['target'], condition: $condition);
    }
}