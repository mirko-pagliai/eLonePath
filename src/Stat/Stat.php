<?php
declare(strict_types=1);

namespace eLonePath\Stat;

use InvalidArgumentException;

/**
 * Base stat class for character attributes (SKILL, STAMINA, LUCK).
 */
abstract class Stat
{
    /**
     * Current value of the stat.
     */
    public int $current {
        set {
            $this->current = max(0, min($value, $this->max));
        }
    }

    /**
     * Maximum value of the stat.
     */
    public int $max {
        set {
            if ($value < 1) {
                throw new InvalidArgumentException('Max value must be at least 1');
            }
            $this->max = $value;
        }
    }

    /**
     * Create a new stat.
     *
     * @param int $max Maximum value
     * @param int|null $current Current value (defaults to max if not provided)
     */
    final public function __construct(int $max, ?int $current = null)
    {
        $this->max = $max;
        $this->current = $current ?? $max;
    }

    /**
     * Decrease the stat by a given amount.
     *
     * @param int $amount
     * @return void
     */
    public function decrease(int $amount): void
    {
        $this->current -= $amount;
    }

    /**
     * Increase the stat by a given amount.
     *
     * @param int $amount
     * @return void
     */
    public function increase(int $amount): void
    {
        $this->current += $amount;
    }

    /**
     * Restore the stat to its maximum value.
     *
     * @return void
     */
    public function restore(): void
    {
        $this->current = $this->max;
    }

    /**
     * Check if the stat is at maximum.
     *
     * @return bool
     */
    public function isAtMax(): bool
    {
        return $this->current === $this->max;
    }

    /**
     * Export stat data to array.
     *
     * @return array{current: int, max: int}
     */
    public function toArray(): array
    {
        return [
            'current' => $this->current,
            'max' => $this->max,
        ];
    }

    /**
     * Create stat from array data.
     *
     * @param array{current: int, max: int} $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new static($data['max'], $data['current']);
    }
}
