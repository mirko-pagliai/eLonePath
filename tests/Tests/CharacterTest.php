<?php
declare(strict_types=1);

namespace eLonePath\Test;

use eLonePath\Character;
use eLonePath\Sex;
use eLonePath\Stat\Luck;
use eLonePath\Stat\Skill;
use eLonePath\Stat\Stamina;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Character::class)]
class CharacterTest extends TestCase
{
    protected Character $character;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->character = new Character(
            name: 'Luke',
            sex: Sex::MALE,
            skill: new Skill(10),
            stamina: new Stamina(21),
            luck: new Luck(8),
        );

        $this->character->addItem('shield');
    }

    #[Test]
    public function testNameProperty(): void
    {
        $this->assertSame('Luke', $this->character->name);

        $this->character->name = ' Han ';
        $this->assertSame('Han', $this->character->name);

        $this->expectExceptionMessage('Character name cannot be empty');
        $this->character->name = '';
    }

    #[Test]
    public function testGoldProperty(): void
    {
        $this->assertSame(10, $this->character->gold);

        $this->character->gold = 15;
        $this->assertSame(15, $this->character->gold);

        $this->expectExceptionMessage('Gold cannot be negative');
        $this->character->gold = -1;
    }

    /**
     * The constructor has already been called in the `setUp()` method, so only the properties set by it are checked here.
     */
    #[Test]
    public function testConstruct(): void
    {
        $this->assertSame('Luke', $this->character->name);
        $this->assertSame(Sex::MALE, $this->character->sex);
        $this->assertSame(Skill::class, get_class($this->character->skill));
        $this->assertSame(Stamina::class, get_class($this->character->stamina));
        $this->assertSame(Luck::class, get_class($this->character->luck));
        $this->assertSame(10, $this->character->gold);
    }

    /**
     * @throws \Random\RandomException
     */
    #[Test]
    public function testCreateWithRolledStats(): void
    {
        $character = Character::createWithRolledStats('Vader', Sex::MALE, 12);
        $this->assertSame('Vader', $character->name);
        $this->assertSame(Sex::MALE, $character->sex);
        $this->assertSame(12, $character->gold);

        $character = Character::createWithRolledStats('Vader', Sex::MALE);
        $this->assertSame('Vader', $character->name);
        $this->assertSame(Sex::MALE, $character->sex);
        $this->assertSame(10, $character->gold);

        $character = Character::createWithRolledStats('Han');
        $this->assertSame('Han', $character->name);
        $this->assertNotEmpty($character->sex);
        $this->assertSame(10, $character->gold);

        $character = Character::createWithRolledStats();
        $this->assertTrue(strlen($character->name) > 0);
        $this->assertNotEmpty($character->sex);
        $this->assertSame(10, $character->gold);
    }

    #[Test]
    public function testIsAlive(): void
    {
        $this->assertTrue($this->character->isAlive());

        $this->character->stamina->current = 1;
        $this->assertTrue($this->character->isAlive());

        $this->character->stamina->current = 0;
        $this->assertFalse($this->character->isAlive());
    }

    #[Test]
    public function testAddItem(): void
    {
        $this->character->addItem('sword');

        $this->assertSame(['shield', 'sword'], $this->character->inventory);
    }

    #[Test]
    public function testRemoveItem(): void
    {
        $result = $this->character->removeItem('shield');
        $this->assertTrue($result);
        $this->assertSame([], $this->character->inventory);

        $result = $this->character->removeItem('shield');
        $this->assertFalse($result);
        $this->assertSame([], $this->character->inventory);
    }

    #[Test]
    public function testHasItem(): void
    {
        $this->assertTrue($this->character->hasItem('shield'));
        $this->assertFalse($this->character->hasItem('sword'));
    }

    #[Test]
    public function testToArray(): void
    {
        $result = $this->character->toArray();
        $this->assertSame([
            'name' => 'Luke',
            'sex' => 'male',
            'skill' => ['current' => 10, 'max' => 10],
            'stamina' => ['current' => 21, 'max' => 21],
            'luck' => ['current' => 8, 'max' => 8],
            'inventory' => ['shield'],
            'gold' => 10,
        ], $result);
    }

    #[Test]
    public function testFromArray(): void
    {
        $character = Character::fromArray([
            'name' => 'Leila',
            'sex' => 'female',
            'skill' => ['current' => 12, 'max' => 12],
            'stamina' => ['current' => 18, 'max' => 18],
            'luck' => ['current' => 14, 'max' => 14],
            'inventory' => ['shield', 'sword'],
            'gold' => 15,
        ]);

        $this->assertSame('Leila', $character->name);
        $this->assertSame(Sex::FEMALE, $character->sex);
        $this->assertSame(12, $character->skill->current);
        $this->assertSame(18, $character->stamina->current);
        $this->assertSame(14, $character->luck->current);
        $this->assertSame(['shield', 'sword'], $character->inventory);
        $this->assertSame(15, $character->gold);
    }
}
