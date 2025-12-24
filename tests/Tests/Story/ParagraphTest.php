<?php
declare(strict_types=1);

namespace eLonePath\Test\Story;

use eLonePath\Story\Choice;
use eLonePath\Story\EventType;
use eLonePath\Story\Paragraph;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Paragraph::class)]
class ParagraphTest extends TestCase
{
    protected Paragraph $paragraph;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->paragraph = new Paragraph(
            id: 1,
            text: 'A text',
            eventType: EventType::ADD_ITEM,
            eventData: ['item' => 'key'],
            choices: [
                new Choice('Choice 1', 2),
                new Choice('Choice 2', 3),
            ],
        );
    }

    #[Test]
    public function testConstruct(): void
    {
        $this->assertSame(1, $this->paragraph->id);
        $this->assertSame('A text', $this->paragraph->text);
        $this->assertSame(EventType::ADD_ITEM, $this->paragraph->eventType);
        $this->assertSame(['item' => 'key'], $this->paragraph->eventData);
        $this->assertEquals([
            new Choice('Choice 1', 2),
            new Choice('Choice 2', 3),
        ], $this->paragraph->choices);
    }

    #[Test]
    #[TestWith(['Paragraph ID must be positive', 0, 'A text'])]
    #[TestWith(['Paragraph ID must be positive', -1, 'A text'])]
    #[TestWith(['Paragraph text cannot be empty', 1, ''])]
    #[TestWith(['Paragraph text cannot be empty', 1, '  '])]
    public function testConstructWithBadArguments(string $expectedExceptionMessage, int $id, string $text): void
    {
        $this->expectExceptionMessage($expectedExceptionMessage);
        new Paragraph($id, $text);
    }

    #[Test]
    public function testHasEvent(): void
    {
        $this->assertTrue($this->paragraph->hasEvent());

        $this->paragraph->eventType = null;
        $this->assertFalse($this->paragraph->hasEvent());
    }

    #[Test]
    public function testIsEnding(): void
    {
        $this->assertFalse($this->paragraph->isEnding());

        $this->paragraph->choices = [];
        $this->assertTrue($this->paragraph->isEnding());
    }

    #[Test]
    public function testToArray(): void
    {
        $expected = [
            'text' => 'A text',
            'event' => [
                'type' => EventType::ADD_ITEM->value,
                'item' => 'key',
            ],
            'choices' => [
                ['text' => 'Choice 1', 'target' => 2],
                ['text' => 'Choice 2', 'target' => 3],
            ],
        ];

        $this->assertSame($expected, $this->paragraph->toArray());

        unset($expected['choices']);
        $this->paragraph->choices = [];
        $this->assertSame($expected, $this->paragraph->toArray());
    }

    #[Test]
    public function testFromArray(): void
    {
        $array = [
            'text' => 'A text',
            'event' => [
                'type' => EventType::ADD_ITEM->value,
                'item' => 'key',
            ],
            'choices' => [
                ['text' => 'Choice 1', 'target' => 2],
                ['text' => 'Choice 2', 'target' => 3],
            ],
        ];

        $paragraph = Paragraph::fromArray(1, $array);
        $this->assertEquals($this->paragraph, $paragraph);

        unset($array['choices']);
        $paragraph = Paragraph::fromArray(1, $array);
        $this->assertEquals([], $paragraph->choices);
    }

    public static function fromArrayOnErrorDataProvider(): Generator
    {
        yield [
            "Paragraph 2 missing 'text'",
            [],
        ];

        yield [
            "Paragraph 2 missing 'text'",
            ['text' => ''],
        ];

        yield [
            'Paragraph text cannot be empty',
            ['text' => '  '],
        ];

        yield [
            "Paragraph 2 event missing 'type'",
            ['text' => 'A text', 'event' => []],
        ];

        yield [
            'Paragraph 2 has invalid event type: `noExistingType`',
            ['text' => 'A text', 'event' => ['type' => 'noExistingType']],
        ];
    }

    /**
     * Tests for the `fromArray()` method when an error is expected.
     *
     * @param string $expectedExceptionMessage The expected exception message when invalid data is provided.
     * @param array{
     *     text: string,
     *     event?: array{type: string, item?: string},
     * } $data The input data that should trigger an error in the Paragraph::fromArray method.
     * @return void
     */
    #[Test]
    #[DataProvider('fromArrayOnErrorDataProvider')]
    public function testFromArrayOnError(string $expectedExceptionMessage, array $data): void
    {
        $this->expectExceptionMessage($expectedExceptionMessage);
        Paragraph::fromArray(2, $data);
    }
}
