<?php
declare(strict_types=1);

namespace App\Story\Combat;

/**
 * Resolves a single round of combat between the player and an enemy — an opposed roll where each side's total is
 * their own already-rolled 2d6 sum plus half their own Agility (rounded down): Agility decides who lands the hit,
 * but its influence is deliberately dampened rather than added in full, so a wide Agility gap doesn't make Strength
 * irrelevant — a strong, clumsy character still keeps a real chance to land (and win) a hit against a fast, weak
 * one. Whoever wins the roll deals damage equal to their own Strength plus a bonus scaled by the margin they won
 * by; a tied roll is a parry, and neither side is hurt.
 *
 * Rolling the actual dice is the caller's job, not this class's — the same way `StoryController::roll()` already
 * rolls for a `DiceNode`'s own check itself, passing only the resulting total into `DiceNode::isSuccess()`. Kept
 * this way here too: `resolveRound()` is a pure function of its arguments, trivial to test against fixed rolls
 * without needing to fake randomness.
 */
final readonly class Combat
{
    /**
     * The floor on how much damage a hit deals, regardless of how narrow the winning margin was — a hit that
     * barely wins should still hurt.
     */
    private const int MINIMUM_DAMAGE = 2;

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
        $playerTotal = $playerRoll + intdiv($player->agility, 2);
        $enemyTotal = $enemyRoll + intdiv($enemy->agility, 2);
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
            $damage = max(self::MINIMUM_DAMAGE, $player->strength + intdiv($delta, 2));

            return new CombatRoundResult(
                hit: CombatHit::Player,
                damage: $damage,
                playerTotal: $playerTotal,
                enemyTotal: $enemyTotal,
                playerLifePoints: $player->lifePoints,
                enemyLifePoints: max(0, $enemy->lifePoints - $damage),
            );
        }

        $damage = max(self::MINIMUM_DAMAGE, $enemy->strength + intdiv(abs($delta), 2));

        return new CombatRoundResult(
            hit: CombatHit::Enemy,
            damage: $damage,
            playerTotal: $playerTotal,
            enemyTotal: $enemyTotal,
            playerLifePoints: max(0, $player->lifePoints - $damage),
            enemyLifePoints: $enemy->lifePoints,
        );
    }
}
