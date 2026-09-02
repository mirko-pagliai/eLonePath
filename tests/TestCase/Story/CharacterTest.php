<?php
declare(strict_types=1);

namespace Test\Story;

use App\Story\Character;
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
    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    public function testConstructWithValidAttributes(): void
    {
        $character = new Character(lifePoints: 20, strength: 6, agility: 8, perception: 3, willpower: 3);

        $this->assertSame(20, $character->lifePoints);
        $this->assertSame(6, $character->strength);
        $this->assertSame(8, $character->agility);
        $this->assertSame(3, $character->perception);
        $this->assertSame(3, $character->willpower);
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
        $character = new Character(lifePoints: 20, strength: 17, agility: 1, perception: 1, willpower: 1);

        $this->assertSame(17, $character->strength);
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    public function testConstructWithLifePointsTooLow(): void
    {
        $this->expectExceptionMessageIs('The lifePoints attribute must be at least 1, got `0`.');
        new Character(lifePoints: 0, strength: 6, agility: 8, perception: 3, willpower: 3);
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    public function testConstructWithStrengthTooLow(): void
    {
        $this->expectExceptionMessageIs('The strength attribute must be at least 1, got `0`.');
        new Character(lifePoints: 20, strength: 0, agility: 14, perception: 3, willpower: 3);
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    public function testConstructWithAgilityTooLow(): void
    {
        $this->expectExceptionMessageIs('The agility attribute must be at least 1, got `0`.');
        new Character(lifePoints: 20, strength: 14, agility: 0, perception: 3, willpower: 3);
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
        new Character(lifePoints: 20, strength: $strength, agility: 8, perception: $perception, willpower: 3);
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
        new Character(lifePoints: 20, strength: $strength, agility: 8, perception: 3, willpower: $willpower);
    }

    /**
     * @link \App\Story\Character::__construct()
     */
    #[Test]
    public function testConstructWithInvalidSum(): void
    {
        $this->expectExceptionMessageIs("The sum of the character's attributes must be 20, got `12`.");
        new Character(lifePoints: 20, strength: 3, agility: 3, perception: 3, willpower: 3);
    }
}
