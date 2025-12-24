<?php
declare(strict_types=1);

namespace eLonePath\Story;

use eLonePath\Character;
use InvalidArgumentException;
use ValueError;

/**
 * Choice in a paragraph.
 *
 * Represents a decision the player can make to navigate to another paragraph.
 */
class Choice
{
    /**
     * Choice text displayed to the player.
     */
    public string $text;

    /**
     * Target paragraph ID.
     */
    public int $target;

    /**
     * Optional condition type for this choice to be available.
     */
    public ?ConditionType $conditionType;

    /**
     * Optional condition data (e.g., item name, stat value).
     *
     * @var array<string, mixed>
     */
    public array $conditionData;

    /**
     * Create a choice.
     *
     * @param string $text Choice text
     * @param int $target Target paragraph ID
     * @param \eLonePath\Story\ConditionType|null $conditionType Optional condition type
     * @param array<string, mixed> $conditionData Optional condition data
     */
    public function __construct(
        string $text,
        int $target,
        ?ConditionType $conditionType = null,
        array $conditionData = []
    ) {
        if (trim($text) === '') {
            throw new InvalidArgumentException('Choice text cannot be empty');
        }
        if ($target < 1) {
            throw new InvalidArgumentException('Target paragraph ID must be positive');
        }

        $this->text = trim($text);
        $this->target = $target;
        $this->conditionType = $conditionType;
        $this->conditionData = $conditionData;
    }

    /**
     * Check if this choice is available based on the game state.
     *
     * @param \eLonePath\Character $character Current character
     * @param bool $combatWon Whether the last combat was won
     * @return bool
     */
    public function isAvailable(Character $character, bool $combatWon = false): bool
    {
        if ($this->conditionType === null) {
            return true;
        }

        if ($this->conditionType === ConditionType::COMBAT_WON) {
            return $combatWon;
        }

        if ($this->conditionType === ConditionType::HAS_ITEM) {
            return $character->hasItem($this->conditionData['item'] ?? '');
        }

        $value = $this->conditionData['value'] ?? 0;

        return match ($this->conditionType) {
            ConditionType::SKILL_GREATER_THAN => $character->skill->isGreaterThan($value),
            ConditionType::STAMINA_GREATER_THAN => $character->stamina->isGreaterThan($value),
            ConditionType::LUCK_GREATER_THAN => $character->luck->isGreaterThan($value),
        };
    }

    /**
     * Export the Choice data to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'text' => $this->text,
            'target' => $this->target,
        ];

        if ($this->conditionType !== null) {
            $data['condition'] = array_merge(
                ['type' => $this->conditionType->value],
                $this->conditionData,
            );
        }

        return $data;
    }

    /**
     * Create the Choice from array data.
     *
     * @param array{
     *     text: string,
     *     target: int,
     *     condition?: array{type: string, item?: string, value?: int}
     * } $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        if (empty($data['text'])) {
            throw new InvalidArgumentException('Choice missing "text"');
        }
        if (empty($data['target'])) {
            throw new InvalidArgumentException('Choice missing "target"');
        }

        if (isset($data['condition'])) {
            if (empty($data['condition']['type'])) {
                throw new InvalidArgumentException('Condition missing "type"');
            }

            try {
                $conditionType = ConditionType::from($data['condition']['type']);
            } catch (ValueError) {
                throw new InvalidArgumentException('Invalid condition type: `' . $data['condition']['type'] . '`');
            }

            unset($data['condition']['type']);
            $conditionData = $data['condition'];
        }

        return new self(
            text: $data['text'],
            target: $data['target'],
            conditionType: $conditionType ?? null,
            conditionData: $conditionData ?? [],
        );
    }
}