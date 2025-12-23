<?php

declare(strict_types=1);

namespace eLonePath\Stat;

use eLonePath\Dice;

/**
 * SKILL stat for character.
 *
 * Represents combat and general ability.
 */
class Skill extends Stat
{
    /**
     * Create a SKILL stat with randomly rolled value (1d6+6).
     *
     * @return self
     * @throws \Random\RandomException
     */
    public static function rollRandom(): self
    {
        $value = Dice::rollD6() + 6; // Range: 7-12

        return new self($value);
    }
}
