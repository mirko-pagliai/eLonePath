<?php
declare(strict_types=1);

namespace Test\Story;

use App\Story\Combat\Combatant;
use App\Story\Enemy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * EnemyTest.
 */
#[CoversClass(Enemy::class)]
class EnemyTest extends TestCase
{
    /**
     * @link \App\Story\Enemy::__construct()
     */
    #[Test]
    public function testConstructWithValidAttributes(): void
    {
        $enemy = new Enemy(name: 'Lupo feroce', maxLifePoints: 10, lifePoints: 10, strength: 6, agility: 8);

        $this->assertSame('Lupo feroce', $enemy->name);
        $this->assertSame(10, $enemy->maxLifePoints);
        $this->assertSame(10, $enemy->lifePoints);
        $this->assertSame(6, $enemy->strength);
        $this->assertSame(8, $enemy->agility);
    }

    /**
     * @link \App\Story\Enemy::__construct()
     */
    #[Test]
    public function testConstructWithMaxLifePointsTooLow(): void
    {
        $this->expectExceptionMessageIs('The maxLifePoints attribute must be at least 1, got `0`.');
        new Enemy(name: 'x', maxLifePoints: 0, lifePoints: 0, strength: 1, agility: 1);
    }

    /**
     * @link \App\Story\Enemy::__construct()
     */
    #[Test]
    public function testConstructWithLifePointsAboveMax(): void
    {
        $this->expectExceptionMessageIs('The lifePoints attribute must be between 0 and maxLifePoints (10), got `15`.');
        new Enemy(name: 'x', maxLifePoints: 10, lifePoints: 15, strength: 1, agility: 1);
    }

    /**
     * @link \App\Story\Enemy::__construct()
     */
    #[Test]
    public function testConstructWithStrengthTooLow(): void
    {
        $this->expectExceptionMessageIs('The strength attribute must be at least 1, got `0`.');
        new Enemy(name: 'x', maxLifePoints: 10, lifePoints: 10, strength: 0, agility: 1);
    }

    /**
     * @link \App\Story\Enemy::__construct()
     */
    #[Test]
    public function testConstructWithAgilityTooLow(): void
    {
        $this->expectExceptionMessageIs('The agility attribute must be at least 1, got `0`.');
        new Enemy(name: 'x', maxLifePoints: 10, lifePoints: 10, strength: 1, agility: 0);
    }

    /**
     * @link \App\Story\Enemy::createNew()
     */
    #[Test]
    public function testCreateNewStartsAtFullLifePoints(): void
    {
        $enemy = Enemy::createNew(name: 'Orco', maxLifePoints: 15, strength: 8, agility: 4);

        $this->assertSame(15, $enemy->maxLifePoints);
        $this->assertSame($enemy->maxLifePoints, $enemy->lifePoints);
    }

    /**
     * @link \App\Story\Enemy::isDefeated()
     */
    #[Test]
    public function testIsDefeated(): void
    {
        $this->assertFalse(Enemy::createNew(name: 'x', maxLifePoints: 10, strength: 1, agility: 1)->isDefeated());

        $defeated = new Enemy(name: 'x', maxLifePoints: 10, lifePoints: 0, strength: 1, agility: 1);
        $this->assertTrue($defeated->isDefeated());
    }

    /**
     * @link \App\Story\Enemy::withDamage()
     */
    #[Test]
    public function testWithDamageReturnsNewInstanceUnchangedOriginal(): void
    {
        $enemy = Enemy::createNew(name: 'x', maxLifePoints: 10, strength: 1, agility: 1);

        $damaged = $enemy->withDamage(4);

        $this->assertNotSame($enemy, $damaged);
        $this->assertSame(10, $enemy->lifePoints);
        $this->assertSame(6, $damaged->lifePoints);
        $this->assertSame($enemy->name, $damaged->name);
    }

    /**
     * @link \App\Story\Enemy::withDamage()
     */
    #[Test]
    public function testWithDamageClampsAtZero(): void
    {
        $enemy = Enemy::createNew(name: 'x', maxLifePoints: 10, strength: 1, agility: 1);

        $damaged = $enemy->withDamage(999);

        $this->assertSame(0, $damaged->lifePoints);
        $this->assertTrue($damaged->isDefeated());
    }

    /**
     * @link \App\Story\Enemy::withDamage()
     */
    #[Test]
    public function testWithDamageRejectsNegativeAmount(): void
    {
        $this->expectExceptionMessageIs('Damage amount must not be negative, got `-1`.');
        Enemy::createNew(name: 'x', maxLifePoints: 10, strength: 1, agility: 1)->withDamage(-1);
    }

    /**
     * @link \App\Story\Enemy::toCombatant()
     */
    #[Test]
    public function testToCombatant(): void
    {
        $enemy = Enemy::createNew(name: 'x', maxLifePoints: 10, strength: 6, agility: 8)->withDamage(3);

        $combatant = $enemy->toCombatant();

        $this->assertInstanceOf(Combatant::class, $combatant);
        $this->assertSame(6, $combatant->strength);
        $this->assertSame(8, $combatant->agility);
        $this->assertSame(7, $combatant->lifePoints);
    }
}
