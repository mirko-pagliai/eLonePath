<?php
declare(strict_types=1);

namespace eLonePath\Test\Story;

use eLonePath\Character;
use eLonePath\Stats\Luck;
use eLonePath\Stats\Skill;
use eLonePath\Stats\Stamina;
use eLonePath\Story\Choice;
use eLonePath\Story\ConditionType;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(Choice::class)]
class ChoiceTest extends TestCase
{
    protected Character&Stub $character;

    protected Choice $choice;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->character = $this->createStub(Character::class);

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
        $this->choice->conditionType = ConditionType::COMBAT_WON;

        $this->assertFalse($this->choice->isAvailable($this->character));
        $this->assertTrue($this->choice->isAvailable($this->character, true));
    }

    /**
     * Tests for the `isAvailable()` method with `ConditionType::HAS_ITEM` condition.
     */
    #[Test]
    public function testIsAvailableForHasItem(): void
    {
        $this->character
            ->method('hasItem')
            ->willReturnCallback(fn(string $item): bool => $item === 'key');

        //The character has the key in his inventory.
        $this->choice->conditionType = ConditionType::HAS_ITEM;
        $this->choice->conditionData['item'] = 'key';
        $this->assertTrue($this->choice->isAvailable($this->character));

        //The character does not have the sword in his inventory.
        $this->choice->conditionType = ConditionType::HAS_ITEM;
        $this->choice->conditionData['item'] = 'sword';
        $this->assertFalse($this->choice->isAvailable($this->character));
    }

    /**
     * Tests for the `isAvailable()` method with `ConditionType::HAS_ITEM` condition, but the item is not valid.
     *
     * @param array{'item': string|null} $conditionData
     */
    #[Test]
    #[TestWith([[]])]
    #[TestWith([['item' => '']])]
    #[TestWith([['item' => ' ']])]
    #[TestWith([['item' => null]])]
    public function testIsAvailableForHasItemWithNotValidItem(array $conditionData): void
    {
        $this->choice->conditionType = ConditionType::HAS_ITEM;
        $this->choice->conditionData = $conditionData;

        $this->expectExceptionMessage('Condition `' . ConditionType::HAS_ITEM->value . '` requires `item` to be set and a string');
        $this->choice->isAvailable($this->character);
    }

    /**
     * Tests for the `isAvailable()` method with `ConditionType::SKILL_GREATER_THAN` condition.
     */
    #[Test]
    public function testIsAvailableWithForSkillGreaterThan(): void
    {
        $this->choice->conditionType = ConditionType::SKILL_GREATER_THAN;
        $this->choice->conditionData['value'] = 8;

        $this->character->skill = $this->createStub(Skill::class);
        $this->character->skill
            ->method('isGreaterThan')
            ->with(8)
            ->willReturn(false);
        $this->assertFalse($this->choice->isAvailable($this->character));

        $this->character->skill = $this->createStub(Skill::class);
        $this->character->skill
            ->method('isGreaterThan')
            ->with(8)
            ->willReturn(true);
        $this->assertTrue($this->choice->isAvailable($this->character));
    }

    /**
     * Tests for the `isAvailable()` method with `ConditionType::STAMINA_GREATER_THAN` condition.
     */
    #[Test]
    public function testIsAvailableWithForStaminaGreaterThan(): void
    {
        $this->choice->conditionType = ConditionType::STAMINA_GREATER_THAN;
        $this->choice->conditionData['value'] = 8;

        $this->character->stamina = $this->createStub(Stamina::class);
        $this->character->stamina
            ->method('isGreaterThan')
            ->with(8)
            ->willReturn(false);
        $this->assertFalse($this->choice->isAvailable($this->character));

        $this->character->stamina = $this->createStub(Stamina::class);
        $this->character->stamina
            ->method('isGreaterThan')
            ->with(8)
            ->willReturn(true);
        $this->assertTrue($this->choice->isAvailable($this->character));
    }

    /**
     * Tests for the `isAvailable()` method with `ConditionType::LUCK_GREATER_THAN` condition.
     */
    #[Test]
    public function testIsAvailableWithForLuckGreaterThan(): void
    {
        $this->choice->conditionType = ConditionType::LUCK_GREATER_THAN;
        $this->choice->conditionData['value'] = 8;

        $this->character->luck = $this->createStub(Luck::class);
        $this->character->luck
            ->method('isGreaterThan')
            ->with(8)
            ->willReturn(false);
        $this->assertFalse($this->choice->isAvailable($this->character));

        $this->character->luck = $this->createStub(Luck::class);
        $this->character->luck
            ->method('isGreaterThan')
            ->with(8)
            ->willReturn(true);
        $this->assertTrue($this->choice->isAvailable($this->character));
    }

    public static function isAvailableWithForGreaterThanWithNotValidValueDataProvider(): Generator
    {
        $conditionsTypes = [
            ConditionType::LUCK_GREATER_THAN,
            ConditionType::SKILL_GREATER_THAN,
            ConditionType::STAMINA_GREATER_THAN,
        ];

        foreach ($conditionsTypes as $ConditionType) {
            yield [[], $ConditionType];
            yield [['value' => ' '], $ConditionType];
            yield [['value' => null], $ConditionType];
            yield [['value' => 'string'], $ConditionType];
            yield [['value' => 0], $ConditionType];
            yield [['value' => -1], $ConditionType];
        }
    }

    /**
     * Tests for the `isAvailable()` method with `ConditionType::LUCK_GREATER_THAN`, `ConditionType::SKILL_GREATER_THAN`
     *  and `ConditionType::STAMINA_GREATER_THAN` conditions, but the value is not valid.
     *
     * @param array{'value': int} $conditionData
     * @param \eLonePath\Story\ConditionType $ConditionType
     */
    #[Test]
    #[DataProvider('isAvailableWithForGreaterThanWithNotValidValueDataProvider')]
    public function testIsAvailableWithForGreaterThanWithNoValidValue(array $conditionData, ConditionType $ConditionType): void
    {
        $this->choice->conditionType = $ConditionType;
        $this->choice->conditionData = $conditionData;

        $this->expectExceptionMessage('Condition `' . $ConditionType->value . '` requires `value` to be set and a positive integer');
        $this->choice->isAvailable($this->character);
    }

    /**
     * Tests for the `isAvailable()` method with no condition type.
     */
    #[Test]
    public function testIsAvailableWithNoConditionType(): void
    {
        $this->choice->conditionType = null;
        $this->assertTrue($this->choice->isAvailable($this->character));
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

    public static function fromArrayOnErrorDataProvider(): Generator
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
