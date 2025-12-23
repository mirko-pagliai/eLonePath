<?php
declare(strict_types=1);

namespace eLonePath\Stat;

use eLonePath\Dice;

/**
 * LUCK stat for character.
 *
 * Represents fortune and chance.
 */
class Luck extends Stat
{
    /**
     * Create a LUCK stat with a randomly rolled value (1d6+6).
     *
     * @return self
     * @throws \Random\RandomException
     */
    public static function rollRandom(): self
    {
        $value = Dice::rollD6() + 6; // Range: 7-12

        return new self($value);
    }

    /**
     * Check LUCK (roll 2d6 <= current LUCK).
     *
     * This method automatically decreases LUCK by 1 after the check.
     *
     * @return bool True if lucky
     * @throws \Random\RandomException
     */
    public function check(): bool
    {
        $roll = Dice::roll2D6();
        $lucky = $roll <= $this->current;

        // Decrease luck by 1 after the check
        $this->decrease(1);

        return $lucky;
    }
}
