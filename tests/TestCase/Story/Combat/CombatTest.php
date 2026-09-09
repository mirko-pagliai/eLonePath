<?php
declare(strict_types=1);

namespace Test\Story\Combat;

use App\Story\Combat\Combat;
use App\Story\Combat\Combatant;
use App\Story\Combat\CombatHit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * CombatTest.
 */
#[CoversClass(Combat::class)]
class CombatTest extends TestCase
{
    /**
     * @link \App\Story\Combat\Combat::resolveRound()
     */
    #[Test]
    public function testResolveRoundPlayerWins(): void
    {
        $player = new Combatant(strength: 10, agility: 6, lifePoints: 20);
        $enemy = new Combatant(strength: 6, agility: 9, lifePoints: 20);

        // playerDiceTotal = (5+3) + intdiv(6,3)=2 -> 10, enemyDiceTotal = (2+2) + intdiv(9,3)=3 -> 7, delta = 3
        $result = Combat::resolveRound($player, $enemy, playerDice: [5, 3], enemyDice: [2, 2]);

        $this->assertSame(CombatHit::Player, $result->hit);
        $this->assertSame([5, 3], $result->playerDice);
        $this->assertSame([2, 2], $result->enemyDice);
        $this->assertSame(10, $result->playerDiceTotal);
        $this->assertSame(7, $result->enemyDiceTotal);
        // damage = player's own strength, flat
        $this->assertSame(10, $result->damage);
        $this->assertSame(20, $result->playerLifePoints);
        $this->assertSame(10, $result->enemyLifePoints);
    }

    /**
     * @link \App\Story\Combat\Combat::resolveRound()
     */
    #[Test]
    public function testResolveRoundEnemyWins(): void
    {
        $player = new Combatant(strength: 10, agility: 6, lifePoints: 20);
        $enemy = new Combatant(strength: 6, agility: 9, lifePoints: 20);

        // playerDiceTotal = (2+2) + 2 = 6, enemyDiceTotal = (5+3) + 3 = 11, delta = -5
        $result = Combat::resolveRound($player, $enemy, playerDice: [2, 2], enemyDice: [5, 3]);

        $this->assertSame(CombatHit::Enemy, $result->hit);
        // damage = enemy's own strength, flat
        $this->assertSame(6, $result->damage);
        $this->assertSame(14, $result->playerLifePoints);
        $this->assertSame(20, $result->enemyLifePoints);
    }

    /**
     * @link \App\Story\Combat\Combat::resolveRound()
     */
    #[Test]
    public function testResolveRoundTieIsAParry(): void
    {
        $player = new Combatant(strength: 10, agility: 6, lifePoints: 20);
        $enemy = new Combatant(strength: 6, agility: 6, lifePoints: 20);

        // playerDiceTotal = (3+2) + 2 = 7, enemyDiceTotal = (3+2) + 2 = 7, delta = 0
        $result = Combat::resolveRound($player, $enemy, playerDice: [3, 2], enemyDice: [3, 2]);

        $this->assertSame(CombatHit::None, $result->hit);
        $this->assertSame(0, $result->damage);
        $this->assertSame(20, $result->playerLifePoints);
        $this->assertSame(20, $result->enemyLifePoints);
    }

    /**
     * Damage is the winner's own Strength, flat — a narrow win and a wide win against the same combatants deal
     * exactly the same damage. This is what actually keeps Agility from doing double duty: it decides who hits,
     * never how hard.
     *
     * @link \App\Story\Combat\Combat::resolveRound()
     */
    #[Test]
    public function testResolveRoundDamageIsFlatRegardlessOfMargin(): void
    {
        $player = new Combatant(strength: 7, agility: 4, lifePoints: 20);
        $enemy = new Combatant(strength: 5, agility: 4, lifePoints: 20);

        // Narrow win: playerDiceTotal = (3+2) + 1 = 6, enemyDiceTotal = (2+2) + 1 = 5, delta = 1
        $narrow = Combat::resolveRound($player, $enemy, playerDice: [3, 2], enemyDice: [2, 2]);
        // Wide win: playerDiceTotal = (6+6) + 1 = 13, enemyDiceTotal = (1+1) + 1 = 3, delta = 10
        $wide = Combat::resolveRound($player, $enemy, playerDice: [6, 6], enemyDice: [1, 1]);

        $this->assertSame(CombatHit::Player, $narrow->hit);
        $this->assertSame(CombatHit::Player, $wide->hit);
        $this->assertSame(7, $narrow->damage);
        $this->assertSame(7, $wide->damage);
    }

    /**
     * Damage never drops a combatant's life points below zero, even when the hit would overkill.
     *
     * @link \App\Story\Combat\Combat::resolveRound()
     */
    #[Test]
    public function testResolveRoundClampsLifePointsAtZero(): void
    {
        $player = new Combatant(strength: 15, agility: 10, lifePoints: 20);
        $enemy = new Combatant(strength: 1, agility: 1, lifePoints: 3);

        // playerDiceTotal = (5+5) + intdiv(10,3)=3 -> 13, enemyDiceTotal = (1+1) + 0 = 2, delta = 11
        $result = Combat::resolveRound($player, $enemy, playerDice: [5, 5], enemyDice: [1, 1]);

        $this->assertSame(CombatHit::Player, $result->hit);
        $this->assertSame(15, $result->damage);
        $this->assertSame(0, $result->enemyLifePoints);
    }
}
