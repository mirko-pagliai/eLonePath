<?php
declare(strict_types=1);

namespace Test\TestCase\Story;

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
     * Strength and Agility each range 4-10 — this locks the boundaries in, rather than leaving them provable
     * only by reading the code.
     *
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    #[TestWith([4, 10])]
    #[TestWith([10, 4])]
    public function testConstructAllowsStrengthAndAgilityAtEitherBoundary(int $strength, int $agility): void
    {
        $character = new Character(
            maxLifePoints: 20,
            lifePoints: 20,
            strength: $strength,
            agility: $agility,
            perception: 3,
            willpower: 3,
        );

        $this->assertSame($strength, $character->strength);
        $this->assertSame($agility, $character->agility);
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    public function testConstructWithMaxLifePointsTooLow(): void
    {
        $this->expectExceptionMessageIs('The maxLifePoints attribute must be at least 1, got 0.');

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
        $this->expectExceptionMessageIs('The lifePoints attribute must be between 0 and maxLifePoints (20), got -1.');

        new Character(maxLifePoints: 20, lifePoints: -1, strength: 10, agility: 6, perception: 2, willpower: 2);
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    public function testConstructWithLifePointsAboveMax(): void
    {
        $this->expectExceptionMessageIs('The lifePoints attribute must be between 0 and maxLifePoints (20), got 25.');

        new Character(maxLifePoints: 20, lifePoints: 25, strength: 10, agility: 6, perception: 2, willpower: 2);
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    #[TestWith([3])]
    #[TestWith([11])]
    public function testConstructWithStrengthOutOfRange(int $strength): void
    {
        $this->expectExceptionMessageIs("The \"strength\" attribute must be between 4 and 10, got $strength.");

        new Character(maxLifePoints: 20, lifePoints: 20, strength: $strength, agility: 7, perception: 3, willpower: 3);
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    #[TestWith([3])]
    #[TestWith([11])]
    public function testConstructWithAgilityOutOfRange(int $agility): void
    {
        $this->expectExceptionMessageIs("The \"agility\" attribute must be between 4 and 10, got $agility.");

        new Character(maxLifePoints: 20, lifePoints: 20, strength: 7, agility: $agility, perception: 3, willpower: 3);
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    #[TestWith([0])]
    #[TestWith([6])]
    public function testConstructWithPerceptionOutOfRange(int $perception): void
    {
        $this->expectExceptionMessageIs("The \"perception\" attribute must be between 1 and 5, got $perception.");

        new Character(
            maxLifePoints: 20,
            lifePoints: 20,
            strength: 5,
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
        $this->expectExceptionMessageIs("The \"willpower\" attribute must be between 1 and 5, got $willpower.");

        new Character(
            maxLifePoints: 20,
            lifePoints: 20,
            strength: 5,
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
        $this->expectExceptionMessageIs("The sum of the character's attributes must be 20, got 10.");

        new Character(maxLifePoints: 20, lifePoints: 20, strength: 4, agility: 4, perception: 1, willpower: 1);
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
     * Every random distribution `createRandom()` picks must itself satisfy `__construct()`'s own validation —
     * running it many times is what actually catches a boundary mistake in the splitting logic that a single
     * lucky call could easily miss.
     *
     * @link \App\Story\Character::createRandom()
     */
    #[Test]
    public function testCreateRandomAlwaysProducesAValidCharacter(): void
    {
        for ($i = 0; $i < 2000; $i++) {
            $character = Character::createRandom(maxLifePoints: 20);

            $this->assertGreaterThanOrEqual(4, $character->strength);
            $this->assertLessThanOrEqual(10, $character->strength);
            $this->assertGreaterThanOrEqual(4, $character->agility);
            $this->assertLessThanOrEqual(10, $character->agility);
            $this->assertGreaterThanOrEqual(1, $character->perception);
            $this->assertLessThanOrEqual(5, $character->perception);
            $this->assertGreaterThanOrEqual(1, $character->willpower);
            $this->assertLessThanOrEqual(5, $character->willpower);
            $this->assertSame(
                20,
                $character->strength + $character->agility + $character->perception + $character->willpower,
            );
        }
    }

    /**
     * `createRandom()` starts at full life points, the same as `createNew()` — it delegates to it for the
     * actual construction, so this is really a check that the delegation happens, not a separate rule of its own.
     *
     * @link \App\Story\Character::createRandom()
     */
    #[Test]
    public function testCreateRandomRespectsGivenMaxLifePoints(): void
    {
        $character = Character::createRandom(maxLifePoints: 30);

        $this->assertSame(30, $character->maxLifePoints);
        $this->assertSame(30, $character->lifePoints);
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

    /**
     * `toArray()` is what `GameState` relies on to carry a `Character` through the URL — this locks in the exact
     * shape it produces, snake_case keys included, since `createFromArray()` on the other end expects precisely
     * this.
     *
     * @link \App\Story\Character::toArray()
     */
    #[Test]
    public function testToArray(): void
    {
        $character = $this->sample(lifePoints: 15);

        $this->assertSame([
            'max_life_points' => 20,
            'life_points' => 15,
            'strength' => 10,
            'agility' => 6,
            'perception' => 2,
            'willpower' => 2,
        ], $character->toArray());
    }

    /**
     * The round trip `GameState` actually depends on: `toArray()` followed by `createFromArray()` reproduces an
     * identical character, not just one that happens to look similar.
     *
     * @link \App\Story\Character::toArray()
     * @link \App\Story\Character::createFromArray()
     */
    #[Test]
    public function testToArrayAndCreateFromArrayRoundTrip(): void
    {
        $original = $this->sample(lifePoints: 13);

        $recreated = Character::createFromArray($original->toArray());

        $this->assertSame($original->maxLifePoints, $recreated->maxLifePoints);
        $this->assertSame($original->lifePoints, $recreated->lifePoints);
        $this->assertSame($original->strength, $recreated->strength);
        $this->assertSame($original->agility, $recreated->agility);
        $this->assertSame($original->perception, $recreated->perception);
        $this->assertSame($original->willpower, $recreated->willpower);
    }

    /**
     * `createFromArray()` doesn't duplicate `__construct()`'s own validation — it delegates to it, so invalid
     * data (an attribute sum that isn't 20, here) is rejected with `Character`'s own message, the same as
     * constructing one directly would.
     *
     * @link \App\Story\Character::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayWithInvalidDataThrows(): void
    {
        $this->expectExceptionMessageIs("The sum of the character's attributes must be 20, got 10.");

        Character::createFromArray([
            'max_life_points' => 20,
            'life_points' => 20,
            'strength' => 4,
            'agility' => 4,
            'perception' => 1,
            'willpower' => 1,
        ]);
    }
}
