<?php
declare(strict_types=1);

namespace eLonePath\Test\Story\DTO;

use eLonePath\Story\DTO\ChoiceDTO;
use eLonePath\Story\DTO\EventDTO;
use eLonePath\Story\DTO\ParagraphDTO;
use eLonePath\Story\EventType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParagraphDTO::class)]
class ParagraphDTOTest extends TestCase
{
    #[Test]
    #[TestWith(['Paragraph ID must be positive.', 0, 'Text for paragraph'])]
    #[TestWith(['Paragraph ID must be positive.', -1, 'Text for paragraph'])]
    #[TestWith(['Paragraph text cannot be empty.', 1, ' '])]
    public function testConstructOnError(string $expectedExceptionMessage, int $id, string $text): void
    {
        $this->expectExceptionMessage($expectedExceptionMessage);
        new ParagraphDTO($id, $text);
    }

    #[Test]
    public function testFromArray(): void
    {
        $paragraph = ParagraphDTO::fromArray(1, ['text' => 'Text for paragraph']);
        $this->assertSame(1, $paragraph->id);
        $this->assertSame('Text for paragraph', $paragraph->text);

        $paragraph = ParagraphDTO::fromArray(1, [
            'text' => 'Text for paragraph',
            'event' => ['type' => EventType::ADD_ITEM->value],
            'choices' => [
                ['text' => 'Choice 1', 'target' => 2],
                ['text' => 'Choice 2', 'target' => 3],
            ],
        ]);

        $this->assertSame('Text for paragraph', $paragraph->text);
        $this->assertSame('Text for paragraph', $paragraph->text);
        $this->assertEquals(new EventDTO(EventType::ADD_ITEM), $paragraph->event);
        $this->assertEquals([
            new ChoiceDTO(text: 'Choice 1', target: 2),
            new ChoiceDTO(text: 'Choice 2', target: 3),
        ], $paragraph->choices);
    }

    /**
     * @param string $expectedExceptionMessage
     * @param array<string, mixed> $data
     */
    #[Test]
    #[TestWith(['Paragraph #1 missing "text".', []])]
    #[TestWith(['Paragraph #1 missing "text".', ['text' => '']])]
    #[TestWith(['Paragraph #1: event missing "type".', ['text' => 'My text', 'event' => []]])]
    #[TestWith(['Paragraph #1: choice missing "text".', ['text' => 'My text', 'choices' => [[]]]])]
    public function fromArrayOnError(string $expectedExceptionMessage, array $data): void
    {
        $this->expectExceptionMessage($expectedExceptionMessage);
        // @phpstan-ignore argument.type
        ParagraphDTO::fromArray(1, $data);
    }
}
