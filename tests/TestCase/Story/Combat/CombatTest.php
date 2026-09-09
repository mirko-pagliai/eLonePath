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

        // playerTotal = 8 + intdiv(6,3)=2 -> 10, enemyTotal = 4 + intdiv(9,3)=3 -> 7, delta = 3
        $result = Combat::resolveRound($player, $enemy, playerRoll: 8, enemyRoll: 4);

        $this->assertSame(CombatHit::Player, $result->hit);
        $this->assertSame(10, $result->playerTotal);
        $this->assertSame(7, $result->enemyTotal);
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

        // playerTotal = 4 + 2 = 6, enemyTotal = 8 + 3 = 11, delta = -5
        $result = Combat::resolveRound($player, $enemy, playerRoll: 4, enemyRoll: 8);

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

        // playerTotal = 5 + 2 = 7, enemyTotal = 5 + 2 = 7, delta = 0
        $result = Combat::resolveRound($player, $enemy, playerRoll: 5, enemyRoll: 5);

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

        // Narrow win: playerTotal = 5 + 1 = 6, enemyTotal = 4 + 1 = 5, delta = 1
        $narrow = Combat::resolveRound($player, $enemy, playerRoll: 5, enemyRoll: 4);
        // Wide win: playerTotal = 12 + 1 = 13, enemyTotal = 2 + 1 = 3, delta = 10
        $wide = Combat::resolveRound($player, $enemy, playerRoll: 12, enemyRoll: 2);

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

        // playerTotal = 10 + intdiv(10,3)=3 -> 13, enemyTotal = 2 + 0 = 2, delta = 11
        $result = Combat::resolveRound($player, $enemy, playerRoll: 10, enemyRoll: 2);

        $this->assertSame(CombatHit::Player, $result->hit);
        $this->assertSame(15, $result->damage);
        $this->assertSame(0, $result->enemyLifePoints);
    }
}
