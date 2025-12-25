<?php
declare(strict_types=1);

namespace eLonePath\Story\DTO;

use eLonePath\Story\ConditionType;
use InvalidArgumentException;
use ValueError;

/**
 * Data Transfer Object for choice conditions from JSON.
 *
 * Immutable object representing condition data parsed from story files.
 */
readonly class ConditionDTO
{
    /**
     * Create a condition DTO.
     *
     * @param \eLonePath\Story\ConditionType $type Condition type
     * @param array<string, mixed> $data Additional condition data (item name, stat value, etc.)
     */
    public function __construct(public ConditionType $type, public array $data = [])
    {
    }

    /**
     * Create ConditionDTO from array data.
     *
     * @param array{type: string, item?: string, value?: int} $data Condition data from JSON
     * @return self
     * @throws \InvalidArgumentException If the type is missing or invalid
     */
    public static function fromArray(array $data): self
    {
        if (empty($data['type'])) {
            throw new InvalidArgumentException('Condition missing "type".');
        }

        try {
            $type = ConditionType::from($data['type']);
        } catch (ValueError) {
            throw new InvalidArgumentException("Invalid condition type: `{$data['type']}`.");
        }

        unset($data['type']);

        return new self(type: $type, data: $data);
    }
}