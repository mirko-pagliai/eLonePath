<?php
declare(strict_types=1);

namespace eLonePath\Test\Story\DTO;

use eLonePath\Story\DTO\StoryMetadataDTO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(StoryMetadataDTO::class)]
class StoryMetadataDTOTest extends TestCase
{
    #[Test]
    #[TestWith(['Story title cannot be empty.', '', 'Mirko'])]
    #[TestWith(['Story title cannot be empty.', ' ', 'Mirko'])]
    #[TestWith(['Story author cannot be empty.', 'Title', ''])]
    #[TestWith(['Story author cannot be empty.', 'Title', ' '])]
    #[TestWith(['Initial gold cannot be negative.', 'Title', 'Mirko', -1])]
    public function testConstructOnError(string $expectedExceptionMessage, string $title, string $author, int $initialGold = 1): void
    {
        $this->expectExceptionMessage($expectedExceptionMessage);
        new StoryMetadataDTO(title: $title, author: $author, initialGold: $initialGold);
    }

    #[Test]
    public function testFromArray(): void
    {
        $story = StoryMetadataDTO::fromArray([
            'title' => 'A title',
            'author' => 'Mirko',
        ]);
        $this->assertSame('A title', $story->title);
        $this->assertSame('Mirko', $story->author);
        $this->assertSame('', $story->description);
        $this->assertSame(10, $story->initialGold);

        $story = StoryMetadataDTO::fromArray([
            'title' => 'A title',
            'author' => 'Mirko',
            'description' => 'A description',
            'initial_gold' => 10,
        ]);
        $this->assertSame('A title', $story->title);
        $this->assertSame('Mirko', $story->author);
        $this->assertSame('A description', $story->description);
        $this->assertSame(10, $story->initialGold);
    }

    /**
     * @param string $expectedExceptionMessage
     * @param array<string, mixed> $data
     */
    #[Test]
    #[TestWith(['Metadata missing "title".', []])]
    #[TestWith(['Metadata missing "title".', ['title' => '']])]
    #[TestWith(['Metadata missing "author".', ['title' => 'Story title']])]
    #[TestWith(['Metadata missing "author".', ['title' => 'Story title', 'author' => '']])]
    public function fromArrayOnError(string $expectedExceptionMessage, array $data): void
    {
        $this->expectExceptionMessage($expectedExceptionMessage);
        // @phpstan-ignore argument.type
        StoryMetadataDTO::fromArray($data);
    }
}
