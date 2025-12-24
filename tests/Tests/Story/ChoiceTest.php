<?php
declare(strict_types=1);

namespace eLonePath\Test\Story;

use eLonePath\Character;
use eLonePath\Stats\Luck;
use eLonePath\Stats\Skill;
use eLonePath\Stats\Stamina;
use eLonePath\Story\Choice;
use eLonePath\Story\ConditionType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Choice::class)]
class ChoiceTest extends TestCase
{
    protected Choice $choice;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->choice = new Choice(
            text: ' Go north ',
            target: 5,
            conditionType: ConditionType::HAS_ITEM,
            conditionData: ['item' => 'key'],
        );
    }

    #[Test]
    public function testConstruct(): void
    {
        $this->assertSame('Go north', $this->choice->text);
        $this->assertSame(5, $this->choice->target);
        $this->assertSame(ConditionType::HAS_ITEM, $this->choice->conditionType);
        $this->assertSame(['item' => 'key'], $this->choice->conditionData);
    }

    /**
     * Tests for the ` _ _construct()` method with bad arguments.
     */
    #[Test]
    #[TestWith(['Choice text cannot be empty', '', 2])]
    #[TestWith(['Choice text cannot be empty', '  ', 2])]
    #[TestWith(['Target paragraph ID must be positive', 'A choice', 0])]
    #[TestWith(['Target paragraph ID must be positive', 'A choice', -1])]
    public function testConstructWithBadArguments(string $expectedExceptionMessage, string $text, int $target): void
    {
        $this->expectExceptionMessage($expectedExceptionMessage);
        new Choice($text, $target);
    }

    /**
     * Tests for the `isAvailable()` method with `ConditionType::COMBAT_WON` condition.
     */
    #[Test]
    public function testIsAvailableForCombatWon(): void
    {
        $character = $this->createStub(Character::class);
        $this->choice->conditionType = ConditionType::COMBAT_WON;

        $this->assertFalse($this->choice->isAvailable($character));
        $this->assertTrue($this->choice->isAvailable($character, true));
    }

    /**
     * Tests for the `isAvailable()` method with `ConditionType::HAS_ITEM` condition.
     */
    #[Test]
    public function testIsAvailableForHasItem(): void
    {
        $this->choice->conditionType = ConditionType::HAS_ITEM;
        $this->choice->conditionData['item'] = 'key';

        $character = $this->createStub(Character::class);
        $character
            ->method('hasItem')
            ->with('key')
            ->willReturn(false);

        $this->assertFalse($this->choice->isAvailable($character));

        $character = $this->createStub(Character::class);
        $character
            ->method('hasItem')
            ->with('key')
            ->willReturn(true);

        $this->assertTrue($this->choice->isAvailable($character));
    }

    /**
     * Tests for the `isAvailable()` method with `ConditionType::SKILL_GREATER_THAN` condition.
     */
    #[Test]
    public function testIsAvailableWithForSkillGreaterThan(): void
    {
        $this->choice->conditionType = ConditionType::SKILL_GREATER_THAN;
        $this->choice->conditionData['value'] = 8;

        $character = $this->createStub(Character::class);

        $character->skill = $this->createStub(Skill::class);
        $character->skill
            ->method('isGreaterThan')
            ->with(8)
            ->willReturn(false);
        $this->assertFalse($this->choice->isAvailable($character));

        $character->skill = $this->createStub(Skill::class);
        $character->skill
            ->method('isGreaterThan')
            ->with(8)
            ->willReturn(true);
        $this->assertTrue($this->choice->isAvailable($character));
    }

    /**
     * Tests for the `isAvailable()` method with `ConditionType::STAMINA_GREATER_THAN` condition.
     */
    #[Test]
    public function testIsAvailableWithForStaminaGreaterThan(): void
    {
        $this->choice->conditionType = ConditionType::STAMINA_GREATER_THAN;
        $this->choice->conditionData['value'] = 8;

        $character = $this->createStub(Character::class);

        $character->stamina = $this->createStub(Stamina::class);
        $character->stamina
            ->method('isGreaterThan')
            ->with(8)
            ->willReturn(false);
        $this->assertFalse($this->choice->isAvailable($character));

        $character->stamina = $this->createStub(Stamina::class);
        $character->stamina
            ->method('isGreaterThan')
            ->with(8)
            ->willReturn(true);
        $this->assertTrue($this->choice->isAvailable($character));
    }

    /**
     * Tests for the `isAvailable()` method with `ConditionType::LUCK_GREATER_THAN` condition.
     */
    #[Test]
    public function testIsAvailableWithForLuckGreaterThan(): void
    {
        $this->choice->conditionType = ConditionType::LUCK_GREATER_THAN;
        $this->choice->conditionData['value'] = 8;

        $character = $this->createStub(Character::class);

        $character->luck = $this->createStub(Luck::class);
        $character->luck
            ->method('isGreaterThan')
            ->with(8)
            ->willReturn(false);
        $this->assertFalse($this->choice->isAvailable($character));

        $character->luck = $this->createStub(Luck::class);
        $character->luck
            ->method('isGreaterThan')
            ->with(8)
            ->willReturn(true);
        $this->assertTrue($this->choice->isAvailable($character));
    }

    /**
     * Tests for the `isAvailable()` method with no condition type.
     */
    #[Test]
    public function testIsAvailableWithNoConditionType(): void
    {
        $character = $this->createStub(Character::class);
        $this->choice->conditionType = null;
        $this->assertTrue($this->choice->isAvailable($character));
    }

    #[Test]
    public function testToArray(): void
    {
        $expected = [
            'text' => 'Go north',
            'target' => 5,
            'condition' => [
                'type' => ConditionType::HAS_ITEM->value,
                'item' => 'key',
            ],
        ];
        $this->assertSame($expected, $this->choice->toArray());

        //Without condition
        $this->choice->conditionType = null;
        unset($expected['condition']);
        $this->assertSame($expected, $this->choice->toArray());
    }

    #[Test]
    public function testFromArray(): void
    {
        $array = [
            'text' => 'Go north',
            'target' => 5,
            'condition' => [
                'type' => ConditionType::HAS_ITEM->value,
                'item' => 'key',
            ],
        ];

        $choice = Choice::fromArray($array);
        $this->assertEquals($this->choice, $choice);
    }

    public static function fromArrayOnErrorDataProvider(): \Generator
    {
        //Missing `target`
        yield [
            'Choice missing "target"',
            ['text' => 'A text'],
        ];

        //Missing `text`
        yield [
            'Choice missing "text"',
            ['target' => 2],
        ];

        //Invalid `text`
        yield [
            'Choice text cannot be empty',
            ['text' => ' ', 'target' => 2],
        ];

        //`condition` is present, but `type` is missing
        yield [
            'Condition missing "type"',
            ['text' => 'A text', 'target' => 2, 'condition' => []],
        ];

        //Invalid `condition` type
        yield [
            'Invalid condition type: `noExistingType`',
            ['text' => 'A text', 'target' => 2, 'condition' => ['type' => 'noExistingType']],
        ];
    }

    /**
     * Tests for the ` fromArray ()` method when an error is expected.
     *
     * @param string $expectedExceptionMessage The exception message that is expected to be thrown.
     * @param array{
     *     text: string,
     *     target: int,
     *     condition?: array{type: string, item?: string, value?: int}
     * } $data An associative array representing the data input for the method.
     * @return void
     */
    #[Test]
    #[DataProvider('fromArrayOnErrorDataProvider')]
    public function testFromArrayOnError(string $expectedExceptionMessage, array $data): void
    {
        $this->expectExceptionMessage($expectedExceptionMessage);
        Choice::fromArray($data);
    }
}
