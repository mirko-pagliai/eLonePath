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
        $enemy = new Combatant(strength: 6, agility: 10, lifePoints: 20);

        // playerTotal = 8 + 3 = 11, enemyTotal = 4 + 5 = 9, delta = 2
        $result = Combat::resolveRound($player, $enemy, playerRoll: 8, enemyRoll: 4);

        $this->assertSame(CombatHit::Player, $result->hit);
        $this->assertSame(11, $result->playerTotal);
        $this->assertSame(9, $result->enemyTotal);
        // damage = max(2, 10 + intdiv(2, 2)) = max(2, 11) = 11
        $this->assertSame(11, $result->damage);
        $this->assertSame(20, $result->playerLifePoints);
        $this->assertSame(9, $result->enemyLifePoints);
    }

    /**
     * @link \App\Story\Combat\Combat::resolveRound()
     */
    #[Test]
    public function testResolveRoundEnemyWins(): void
    {
        $player = new Combatant(strength: 10, agility: 6, lifePoints: 20);
        $enemy = new Combatant(strength: 6, agility: 10, lifePoints: 20);

        // playerTotal = 4 + 3 = 7, enemyTotal = 5 + 5 = 10, delta = -3
        $result = Combat::resolveRound($player, $enemy, playerRoll: 4, enemyRoll: 5);

        $this->assertSame(CombatHit::Enemy, $result->hit);
        // damage = max(2, 6 + intdiv(3, 2)) = max(2, 7) = 7
        $this->assertSame(7, $result->damage);
        $this->assertSame(13, $result->playerLifePoints);
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

        // playerTotal = 5 + 3 = 8, enemyTotal = 5 + 3 = 8, delta = 0
        $result = Combat::resolveRound($player, $enemy, playerRoll: 5, enemyRoll: 5);

        $this->assertSame(CombatHit::None, $result->hit);
        $this->assertSame(0, $result->damage);
        $this->assertSame(20, $result->playerLifePoints);
        $this->assertSame(20, $result->enemyLifePoints);
    }

    /**
     * Even the narrowest possible winning margin still deals the minimum damage floor, not less.
     *
     * @link \App\Story\Combat\Combat::resolveRound()
     */
    #[Test]
    public function testResolveRoundAppliesMinimumDamageFloor(): void
    {
        $player = new Combatant(strength: 1, agility: 1, lifePoints: 20);
        $enemy = new Combatant(strength: 1, agility: 1, lifePoints: 20);

        // playerTotal = 3 + 0 = 3, enemyTotal = 2 + 0 = 2, delta = 1 -> strength(1) + intdiv(1,2)=0 -> raw damage 1
        $result = Combat::resolveRound($player, $enemy, playerRoll: 3, enemyRoll: 2);

        $this->assertSame(CombatHit::Player, $result->hit);
        $this->assertSame(2, $result->damage);
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

        // playerTotal = 12 + 5 = 17, enemyTotal = 2 + 0 = 2, delta = 15 -> damage = max(2, 15 + 7) = 22
        $result = Combat::resolveRound($player, $enemy, playerRoll: 12, enemyRoll: 2);

        $this->assertSame(CombatHit::Player, $result->hit);
        $this->assertSame(22, $result->damage);
        $this->assertSame(0, $result->enemyLifePoints);
    }
}
