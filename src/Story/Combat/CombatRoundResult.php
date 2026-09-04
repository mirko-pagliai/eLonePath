<?php
declare(strict_types=1);

namespace App\Story\Combat;

/**
 * The outcome of a single combat round — see `Combat::resolveRound()`. `playerTotal`/`enemyTotal` are each side's
 * full roll (dice plus their own Agility bonus), kept here for narration — a template can show them the same way
 * `templates/Story/roll.php` already shows a `DiceNode` check's rolls and total.
 */
final readonly class CombatRoundResult
{
    public function __construct(
        public CombatHit $hit,
        public int $damage,
        public int $playerTotal,
        public int $enemyTotal,
        public int $playerLifePoints,
        public int $enemyLifePoints,
    ) {
    }
}
