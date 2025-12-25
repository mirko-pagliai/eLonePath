<?php
declare(strict_types=1);

namespace eLonePath\Engine\Utility;

/**
 * Provides methods to simulate rolling six-sided dice.
 *
 * Handles all dice rolling mechanics for the game engine.
 */
class Dice
{
    /**
     * Roll a single six-sided die.
     *
     * @return positive-int Result between 1 and 6
     * @throws \Random\RandomException
     */
    public static function rollD6(): int
    {
        return random_int(1, 6);
    }

    /**
     * Roll two six-sided dice.
     *
     * @return positive-int Result between 2 and 12
     * @throws \Random\RandomException
     */
    public static function roll2D6(): int
    {
        return self::rollD6() + self::rollD6();
    }

    /**
     * Rolls a six-sided die multiple times and returns the total sum of all rolls.
     *
     * @param positive-int $count The number of dice rolls to perform. Must be greater than 0.
     * @return positive-int The total sum of the results of the dice rolls.
     * @throws \InvalidArgumentException If the number of rolls is less than 1.
     * @throws \Random\RandomException
     */
    public static function rollMultipleD6(int $count): int
    {
        if ($count < 1) {
            throw new \InvalidArgumentException('Number of rolls must be greater than 0.');
        }

        $total = 0;
        for ($i = 0; $i < $count; $i++) {
            $total += self::rollD6();
        }

        return $total;
    }
}
