<?php
declare(strict_types=1);

namespace eLonePath\Test\Story;

use eLonePath\Story\Choice;
use PHPUnit\Framework\Attributes\CoversClass;
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
        $this->choice = new Choice('A choice', 1, ['a' => 'Condition A', 'b' => 'Condition B']);
    }

    #[Test]
    public function testConstruct(): void
    {
        $this->assertSame('A choice', $this->choice->text);
        $this->assertSame(1, $this->choice->target);
        $this->assertSame(['a' => 'Condition A', 'b' => 'Condition B'], $this->choice->condition);
    }

    /**
     * Tests for `__construct()` method, with bad arguments.
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

    #[Test]
    public function testToArray(): void
    {
        $expected = ['text' => 'A choice', 'target' => 1, 'condition' => ['a' => 'Condition A', 'b' => 'Condition B']];
        $this->assertSame($expected, $this->choice->toArray());
    }

    #[Test]
    public function testFromArray(): void
    {
        $choice = Choice::fromArray(['text' => '  Another choice ', 'target' => 6, 'condition' => ['c' => 'Condition C', 'd' => 'Condition D']]);
        $this->assertSame('Another choice', $choice->text);
        $this->assertSame(6, $choice->target);
        $this->assertSame(['c' => 'Condition C', 'd' => 'Condition D'], $choice->condition);
    }

    #[Test]
    #[TestWith(['Choice missing "target"', ['text' => 'A text']])]
    #[TestWith(['Choice missing "text"', ['target' => 2]])]
    #[TestWith(['Choice text cannot be empty', ['text' => ' ', 'target' => 2]])]
    public function testFromArrayOnError(string $expectedExceptionMessage, array $data): void
    {
        $this->expectExceptionMessage($expectedExceptionMessage);
        // @phpstan-ignore argument.type
        Choice::fromArray($data);
    }
}
