<?php
declare(strict_types=1);

namespace eLonePath;

/**
 * Dice rolling utility class.
 *
 * Handles all dice rolling mechanics for the game engine.
 */
class Dice
{
    /**
     * Roll a single six-sided die.
     *
     * @return int Result between 1 and 6
     * @throws \Random\RandomException
     */
    public static function rollD6(): int
    {
        return random_int(1, 6);
    }

    /**
     * Roll two six-sided dice.
     *
     * @return int Result between 2 and 12
     * @throws \Random\RandomException
     */
    public static function roll2D6(): int
    {
        return self::rollD6() + self::rollD6();
    }

    /**
     * Roll multiple six-sided dice.
     *
     * @param int $count Number of dice to roll
     * @return int Sum of all dice
     * @throws \Random\RandomException
     */
    public static function rollMultipleD6(int $count): int
    {
        $total = 0;
        for ($i = 0; $i < $count; $i++) {
            $total += self::rollD6();
        }
        return $total;
    }
}
