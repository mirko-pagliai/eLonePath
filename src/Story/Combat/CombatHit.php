<?php
declare(strict_types=1);

namespace App\Story\Combat;

/**
 * Who landed the hit in a single combat round — see `Combat::resolveRound()`.
 */
enum CombatHit
{
    /**
     * The player's roll beat the enemy's; the enemy took the damage.
     */
    case Player;

    /**
     * The enemy's roll beat the player's; the player took the damage.
     */
    case Enemy;

    /**
     * The two rolls tied — a parry. Neither side is damaged this round.
     */
    case None;
}
