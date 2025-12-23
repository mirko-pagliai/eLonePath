<?php
declare(strict_types=1);

namespace eLonePath\Stats;

use eLonePath\Dice;

/**
 * STAMINA stat for character.
 *
 * Represents health and endurance.
 */
class Stamina extends Stat
{
    /**
     * Create a STAMINA stat with randomly rolled value (2d6+12).
     *
     * @return self
     * @throws \Random\RandomException
     */
    public static function rollRandom(): self
    {
        $value = Dice::roll2D6() + 12; // Range: 14-24

        return new self($value);
    }
}
