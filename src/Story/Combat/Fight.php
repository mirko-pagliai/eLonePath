<?php
declare(strict_types=1);

namespace App\Story\Combat;

use App\Story\Character;
use App\Story\Enemy;
use App\Utility\Dice;

/**
 * Resolves a whole fight, round by round, calling `Combat::resolveRound()` until one side is defeated — the same
 * way a reader of the paper original resolves combat entirely on their character sheet, never turning a page
 * between rounds, only once the fight itself is over. `resolve()` mirrors that: it runs to completion in one call,
 * returning every round's result together with the final state of both sides.
 *
 * Unlike `Combat::resolveRound()`, this class can't stay a pure function of pre-rolled numbers — the number of
 * rounds a fight takes isn't known ahead of time, so it has to roll the dice itself as it goes. `$rollTwoD6` is
 * the escape hatch that keeps it testable anyway: real fights use the default (real 2d6), tests inject a fixed
 * sequence instead of faking randomness.
 */
final readonly class Fight
{
    /**
     * A safety cap, not a design target — see `FightResult::isDraw()`. Real fights resolve in a handful of
     * rounds; this exists only so a pathological case (once weapons exist) can't loop forever.
     */
    private const int MAX_ROUNDS = 50;

    /**
     * @param \App\Story\Character $player The player, at whatever life points they currently have.
     * @param \App\Story\Enemy $enemy The enemy, freshly built for this encounter.
     * @param (callable(): int)|null $rollTwoD6 Rolls one side's 2d6 total (2-12) for one round. Defaults to real
     *  dice (`array_sum(new Dice()->rollDouble())`); called twice per round, once per side.
     * @return \App\Story\Combat\FightResult
     */
    public static function resolve(Character $player, Enemy $enemy, ?callable $rollTwoD6 = null): FightResult
    {
        $rollTwoD6 ??= static fn(): int => array_sum(new Dice()->rollDouble());

        $rounds = [];
        $roundCount = 0;

        while (!$player->isDefeated() && !$enemy->isDefeated() && $roundCount < self::MAX_ROUNDS) {
            $result = Combat::resolveRound(
                player: $player->toCombatant(),
                enemy: $enemy->toCombatant(),
                playerRoll: $rollTwoD6(),
                enemyRoll: $rollTwoD6(),
            );

            $rounds[] = $result;
            $roundCount++;

            if ($result->hit === CombatHit::Player) {
                $enemy = $enemy->withDamage($result->damage);
            } elseif ($result->hit === CombatHit::Enemy) {
                $player = $player->withDamage($result->damage);
            }
        }

        return new FightResult(rounds: $rounds, player: $player, enemy: $enemy);
    }
}
