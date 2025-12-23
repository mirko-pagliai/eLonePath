<?php
declare(strict_types=1);

namespace eLonePath\Story;

use eLonePath\Character;
use InvalidArgumentException;

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
     * Optional condition for this choice to be available.
     *
     * @var array<string, mixed>|null
     */
    public ?array $condition;

    /**
     * Create a choice.
     *
     * @param string $text Choice text
     * @param int $target Target paragraph ID
     * @param array<string, mixed>|null $condition Optional condition
     */
    public function __construct(string $text, int $target, ?array $condition = null)
    {
        if (trim($text) === '') {
            throw new InvalidArgumentException('Choice text cannot be empty');
        }
        if ($target < 1) {
            throw new InvalidArgumentException('Target paragraph ID must be positive');
        }

        $this->text = trim($text);
        $this->target = $target;
        $this->condition = $condition;
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
        if ($this->condition === null) {
            return true;
        }

        $type = $this->condition['type'] ?? null;

        return match ($type) {
            'combat_won' => $combatWon,
            'has_item' => $character->hasItem($this->condition['item'] ?? ''),
            'skill_greater_than' => $character->skill->current > ($this->condition['value'] ?? 0),
            'stamina_greater_than' => $character->stamina->current > ($this->condition['value'] ?? 0),
            'luck_greater_than' => $character->luck->current > ($this->condition['value'] ?? 0),
            default => true,
        };
    }

    /**
     * Export choice data to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'text' => $this->text,
            'target' => $this->target,
        ];

        if ($this->condition !== null) {
            $data['condition'] = $this->condition;
        }

        return $data;
    }

    /**
     * Create a choice instance from an array of data.
     *
     * @param array{
     *      text: string,
     *      target: int,
     *      condition: array<string, mixed>|null,
     * } $data Associative array containing choice data with keys 'text', 'target', and optionally 'condition'
     * @return self Returns an instance of the current class
     * @throws \InvalidArgumentException If the 'text' or 'target' keys are missing or empty
     */
    public static function fromArray(array $data): self
    {
        if (empty($data['text'])) {
            throw new InvalidArgumentException('Choice missing "text"');
        }
        if (empty($data['target'])) {
            throw new InvalidArgumentException('Choice missing "target"');
        }

        return new self(text: $data['text'] , target: $data['target'], condition: $data['condition'] ?? null);
    }
}
