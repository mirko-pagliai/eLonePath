<?php
declare(strict_types=1);

namespace App\Utility;

/**
 * Represents a dice that can be rolled to generate random values.
 */
final class Dice
{
    /**
     * Rolls the dice and returns the result.
     *
     * @return int
     * @throws \Random\RandomException
     */
    public function roll(): int
    {
        return random_int(1, 6);
    }

    /**
     * Rolls the dice twice and returns the results as an array.
     *
     * @return array{int, int}
     * @throws \Random\RandomException
     */
    public function rollDouble(): array
    {
        return [
            $this->roll(),
            $this->roll(),
        ];
    }

    /**
     * Rolls the dice `$count` times and returns every result, in order.
     *
     * @return list<int>
     * @throws \Random\RandomException
     */
    public function rollMultiple(int $count): array
    {
        $rolls = [];

        for ($i = 0; $i < $count; $i++) {
            $rolls[] = $this->roll();
        }

        return $rolls;
    }
}
