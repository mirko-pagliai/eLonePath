<?php
declare(strict_types=1);

namespace Test\Story;

use App\Story\Character;
use App\Story\Combat\Combatant;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * CharacterTest.
 */
#[CoversClass(Character::class)]
class CharacterTest extends TestCase
{
    private function sample(int $lifePoints = 20): Character
    {
        return new Character(
            maxLifePoints: 20,
            lifePoints: $lifePoints,
            strength: 10,
            agility: 6,
            perception: 2,
            willpower: 2,
        );
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    public function testConstructWithValidAttributes(): void
    {
        $character = $this->sample();

        $this->assertSame(20, $character->maxLifePoints);
        $this->assertSame(20, $character->lifePoints);
        $this->assertSame(10, $character->strength);
        $this->assertSame(6, $character->agility);
        $this->assertSame(2, $character->perception);
        $this->assertSame(2, $character->willpower);
    }

    /**
     * Strength and Agility have no upper bound of their own — only the sum constraint limits them. This locks
     * that in, rather than leaving it provable only by reading the code.
     *
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    public function testConstructAllowsStrengthOrAgilityAboveFive(): void
    {
        $character = new Character(
            maxLifePoints: 20,
            lifePoints: 20,
            strength: 17,
            agility: 1,
            perception: 1,
            willpower: 1,
        );

        $this->assertSame(17, $character->strength);
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    public function testConstructWithMaxLifePointsTooLow(): void
    {
        $this->expectExceptionMessageIs('The maxLifePoints attribute must be at least 1, got `0`.');
        new Character(maxLifePoints: 0, lifePoints: 0, strength: 10, agility: 6, perception: 2, willpower: 2);
    }

    /**
     * `lifePoints` reaching `0` is a valid state — a defeated character, not a construction error. Only a
     * negative value, or one exceeding `maxLifePoints`, is rejected.
     *
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    public function testConstructAllowsZeroLifePoints(): void
    {
        $character = $this->sample(lifePoints: 0);

        $this->assertSame(0, $character->lifePoints);
        $this->assertTrue($character->isDefeated());
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    public function testConstructWithNegativeLifePoints(): void
    {
        $this->expectExceptionMessageIs(
            'The lifePoints attribute must be between 0 and maxLifePoints (20), got `-1`.',
        );
        new Character(maxLifePoints: 20, lifePoints: -1, strength: 10, agility: 6, perception: 2, willpower: 2);
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    public function testConstructWithLifePointsAboveMax(): void
    {
        $this->expectExceptionMessageIs(
            'The lifePoints attribute must be between 0 and maxLifePoints (20), got `25`.',
        );
        new Character(maxLifePoints: 20, lifePoints: 25, strength: 10, agility: 6, perception: 2, willpower: 2);
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    public function testConstructWithStrengthTooLow(): void
    {
        $this->expectExceptionMessageIs('The strength attribute must be at least 1, got `0`.');
        new Character(maxLifePoints: 20, lifePoints: 20, strength: 0, agility: 14, perception: 3, willpower: 3);
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    public function testConstructWithAgilityTooLow(): void
    {
        $this->expectExceptionMessageIs('The agility attribute must be at least 1, got `0`.');
        new Character(maxLifePoints: 20, lifePoints: 20, strength: 14, agility: 0, perception: 3, willpower: 3);
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    #[TestWith([0])]
    #[TestWith([6])]
    public function testConstructWithPerceptionOutOfRange(int $perception): void
    {
        $strength = 20 - 8 - $perception - 3;
        $this->expectExceptionMessageIs("The perception attribute must be between 1 and 5, got `$perception`.");
        new Character(
            maxLifePoints: 20,
            lifePoints: 20,
            strength: $strength,
            agility: 8,
            perception: $perception,
            willpower: 3,
        );
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    #[TestWith([0])]
    #[TestWith([6])]
    public function testConstructWithWillpowerOutOfRange(int $willpower): void
    {
        $strength = 20 - 8 - 3 - $willpower;
        $this->expectExceptionMessageIs("The willpower attribute must be between 1 and 5, got `$willpower`.");
        new Character(
            maxLifePoints: 20,
            lifePoints: 20,
            strength: $strength,
            agility: 8,
            perception: 3,
            willpower: $willpower,
        );
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    public function testConstructWithInvalidSum(): void
    {
        $this->expectExceptionMessageIs("The sum of the character's attributes must be 20, got `12`.");
        new Character(maxLifePoints: 20, lifePoints: 20, strength: 3, agility: 3, perception: 3, willpower: 3);
    }

    /**
     * Unlike the regular constructor, `createNew()` doesn't take `lifePoints` at all — it always starts equal to
     * `maxLifePoints`, guaranteed by the method itself rather than left to the caller to get right.
     *
     * @link \App\Story\Character::createNew()
     */
    #[Test]
    public function testCreateNewStartsAtFullLifePoints(): void
    {
        $character = Character::createNew(maxLifePoints: 20, strength: 10, agility: 6, perception: 2, willpower: 2);

        $this->assertSame(20, $character->maxLifePoints);
        $this->assertSame($character->maxLifePoints, $character->lifePoints);
    }

    /**
     * @link \App\Story\Character::isDefeated()
     */
    #[Test]
    public function testIsDefeated(): void
    {
        $this->assertFalse($this->sample(lifePoints: 1)->isDefeated());
        $this->assertTrue($this->sample(lifePoints: 0)->isDefeated());
    }

    /**
     * `withDamage()` returns a new instance, leaving the original untouched — the immutable pattern every other
     * domain object in this codebase already follows.
     *
     * @link \App\Story\Character::withDamage()
     */
    #[Test]
    public function testWithDamageReturnsNewInstanceUnchangedOriginal(): void
    {
        $character = $this->sample();

        $damaged = $character->withDamage(7);

        $this->assertNotSame($character, $damaged);
        $this->assertSame(20, $character->lifePoints);
        $this->assertSame(13, $damaged->lifePoints);
    }

    /**
     * @link \App\Story\Character::withDamage()
     */
    #[Test]
    public function testWithDamageClampsAtZero(): void
    {
        $character = $this->sample();

        $damaged = $character->withDamage(999);

        $this->assertSame(0, $damaged->lifePoints);
        $this->assertTrue($damaged->isDefeated());
    }

    /**
     * @link \App\Story\Character::withDamage()
     */
    #[Test]
    public function testWithDamageRejectsNegativeAmount(): void
    {
        $this->expectExceptionMessageIs('Damage amount must not be negative, got `-1`.');
        $this->sample()->withDamage(-1);
    }

    /**
     * Healing can restore life points, but never past the character's own starting maximum — the requirement
     * this class exists to enforce in one place, rather than leaving every caller to remember the cap.
     *
     * @link \App\Story\Character::withHeal()
     */
    #[Test]
    public function testWithHealClampsAtMaxLifePoints(): void
    {
        $hurt = $this->sample(lifePoints: 5);

        $overhealed = $hurt->withHeal(999);

        $this->assertSame(20, $overhealed->lifePoints);
        $this->assertSame($overhealed->maxLifePoints, $overhealed->lifePoints);
    }

    /**
     * @link \App\Story\Character::withHeal()
     */
    #[Test]
    public function testWithHealPartial(): void
    {
        $hurt = $this->sample(lifePoints: 5);

        $healed = $hurt->withHeal(3);

        $this->assertSame(8, $healed->lifePoints);
    }

    /**
     * @link \App\Story\Character::withHeal()
     */
    #[Test]
    public function testWithHealRejectsNegativeAmount(): void
    {
        $this->expectExceptionMessageIs('Heal amount must not be negative, got `-1`.');
        $this->sample()->withHeal(-1);
    }

    /**
     * @link \App\Story\Character::toCombatant()
     */
    #[Test]
    public function testToCombatant(): void
    {
        $character = $this->sample()->withDamage(5);

        $combatant = $character->toCombatant();

        $this->assertInstanceOf(Combatant::class, $combatant);
        $this->assertSame(10, $combatant->strength);
        $this->assertSame(6, $combatant->agility);
        // The current life points, not maxLifePoints.
        $this->assertSame(15, $combatant->lifePoints);
    }
}
