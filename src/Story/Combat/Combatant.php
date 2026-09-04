<?php
declare(strict_types=1);

namespace App\Story\Combat;

/**
 * The three numbers `Combat::resolveRound()` needs from either side of a fight — a plain snapshot, not tied to
 * `Character` itself. Once weapons exist, a `Combatant` is where their bonuses actually land: whoever builds one
 * (today, `Character::toCombatant()`; later, that plus whatever an equipped weapon adds) decides what `strength`
 * and `agility` mean here, and `Combat` never has to know the difference.
 */
final readonly class Combatant
{
    public function __construct(
        public int $strength,
        public int $agility,
        public int $lifePoints,
    ) {
    }
}
