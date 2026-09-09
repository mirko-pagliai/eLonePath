<?php
declare(strict_types=1);

namespace App\Story\Combat;

/**
 * The outcome of a single combat round — see `Combat::resolveRound()`. `playerDice`/`enemyDice` are each side's
 * individual 2d6 roll, for a template to render as separate dice (the same way `templates/Story/roll.php`
 * already renders a `DiceNode` check's own `rolls`); `playerDiceTotal`/`enemyDiceTotal` are each side's full
 * roll — dice plus their own Agility bonus — kept for both narration and the calculation that decided this round.
 */
final readonly class CombatRoundResult
{
    /**
     * @param array{int, int} $playerDice
     * @param array{int, int} $enemyDice
     */
    public function __construct(
        public CombatHit $hit,
        public int $damage,
        public array $playerDice,
        public array $enemyDice,
        public int $playerDiceTotal,
        public int $enemyDiceTotal,
        public int $playerLifePoints,
        public int $enemyLifePoints,
    ) {
    }
}
