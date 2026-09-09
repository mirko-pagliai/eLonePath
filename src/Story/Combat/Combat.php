<?php
declare(strict_types=1);

namespace App\Story\Combat;

/**
 * Resolves a single round of combat between the player and an enemy — an opposed roll where each side's total is
 * their own already-rolled 2d6 sum plus a third of their own Agility (rounded down): Agility decides who lands
 * the hit, nothing else. Whoever wins deals damage equal to their own Strength, flat — not scaled by how wide the
 * winning margin was. The two attributes are deliberately kept from overlapping: Agility only ever affects who
 * hits, Strength only ever affects how hard, so neither one can double up on the other's job. A tied roll is a
 * parry; neither side is hurt.
 *
 * Rolling the actual dice is the caller's job, not this class's — the same way `StoryController::roll()` already
 * rolls for a `DiceNode`'s own check itself, passing only the resulting total into `DiceNode::isSuccess()`. Kept
 * this way here too: `resolveRound()` is a pure function of its arguments, trivial to test against fixed rolls
 * without needing to fake randomness.
 */
final readonly class Combat
{
    /**
     * @param \App\Story\Combat\Combatant $player The player's stats for this round.
     * @param \App\Story\Combat\Combatant $enemy The enemy's stats for this round.
     * @param int $playerRoll The player's already-rolled 2d6 sum (2-12).
     * @param int $enemyRoll The enemy's already-rolled 2d6 sum (2-12).
     */
    public static function resolveRound(
        Combatant $player,
        Combatant $enemy,
        int $playerRoll,
        int $enemyRoll,
    ): CombatRoundResult {
        $playerTotal = $playerRoll + intdiv($player->agility, 3);
        $enemyTotal = $enemyRoll + intdiv($enemy->agility, 3);
        $delta = $playerTotal - $enemyTotal;

        if ($delta === 0) {
            return new CombatRoundResult(
                hit: CombatHit::None,
                damage: 0,
                playerTotal: $playerTotal,
                enemyTotal: $enemyTotal,
                playerLifePoints: $player->lifePoints,
                enemyLifePoints: $enemy->lifePoints,
            );
        }

        if ($delta > 0) {
            return new CombatRoundResult(
                hit: CombatHit::Player,
                damage: $player->strength,
                playerTotal: $playerTotal,
                enemyTotal: $enemyTotal,
                playerLifePoints: $player->lifePoints,
                enemyLifePoints: max(0, $enemy->lifePoints - $player->strength),
            );
        }

        return new CombatRoundResult(
            hit: CombatHit::Enemy,
            damage: $enemy->strength,
            playerTotal: $playerTotal,
            enemyTotal: $enemyTotal,
            playerLifePoints: max(0, $player->lifePoints - $enemy->strength),
            enemyLifePoints: $enemy->lifePoints,
        );
    }
}
