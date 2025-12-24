<?php
declare(strict_types=1);

namespace eLonePath\Test\Story;

use eLonePath\Story\Choice;
use eLonePath\Story\Paragraph;
use eLonePath\Story\Story;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Story::class)]
class StoryTest extends TestCase
{
    protected Story $story;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->story = new Story(
            ' A title ',
            ' Mirko ',
            ' A description ',
            10,
            [
                new Paragraph(1, 'A paragraph'),
                new Paragraph(2, 'Another paragraph'),
            ],
        );
    }

    #[Test]
    public function testConstruct(): void
    {
        $expectedParagraphs = [
            1 => new Paragraph(1, 'A paragraph'),
            2 => new Paragraph(2, 'Another paragraph'),
        ];

        $this->assertSame('A title', $this->story->title);
        $this->assertSame('Mirko', $this->story->author);
        $this->assertSame('A description', $this->story->description);
        $this->assertSame(10, $this->story->initialGold);
        $this->assertEquals($expectedParagraphs, $this->story->getAllParagraphs());
    }

    #[Test]
    #[TestWith(['Story title cannot be empty', '', 'Mirko', 1])]
    #[TestWith(['Story title cannot be empty', '  ', 'Mirko', 1])]
    #[TestWith(['Story author cannot be empty', 'A title', '', 1])]
    #[TestWith(['Initial gold cannot be negative', 'A title', 'Mirko', -1])]
    public function testConstructWithBadArguments(
        string $expectedExceptionMessage,
        string $title,
        string $author,
        int $initialGold,
    ): void {
        $this->expectExceptionMessage($expectedExceptionMessage);
        new Story($title, $author, 'A description', $initialGold);
    }

    #[Test]
    public function testAddParagraph(): void
    {
        $this->assertFalse($this->story->hasParagraph(3));

        $newParagraph = new Paragraph(3, 'A new paragraph');
        $result = $this->story->addParagraph($newParagraph);
        $this->assertEquals($this->story, $result);
        $this->assertSame($newParagraph, $this->story->getParagraph(3));
    }

    #[Test]
    public function testGetParagraph(): void
    {
        $paragraph = $this->story->getParagraph(2);
        $this->assertSame(2, $paragraph->id);
    }

    #[Test]
    public function testGetParagraphWithNoExistingParagraph(): void
    {
        $this->expectExceptionMessage('Paragraph with ID `3` does not exist in this story');
        $this->story->getParagraph(3);
    }

    #[Test]
    public function testHasParagraph(): void
    {
        $this->assertTrue($this->story->hasParagraph(2));
        $this->assertFalse($this->story->hasParagraph(3));
    }

    #[Test]
    public function testGetAllParagraphs(): void
    {
        $paragraphs = $this->story->getAllParagraphs();
        $this->assertCount(2, $paragraphs);
    }

    #[Test]
    public function testGetStartingParagraph(): void
    {
        $paragraph = $this->story->getStartingParagraph();
        $this->assertSame(1, $paragraph->id);
    }

    #[Test]
    public function testValidate(): void
    {
        $story = new Story('A title', 'Mirko', 'A description', 10);

        //The story we just created does not contain any paragraphs
        $expected = ['Story must have a starting paragraph with ID `#1`'];
        $this->assertSame($expected, $story->validate());

        //Paragraph #3 is added, but the error is the same.
        $story->addParagraph(new Paragraph(id: 3, text: 'Third paragraph'));
        $this->assertSame($expected, $story->validate());

        //Paragraph #1 is added, but there's a link to paragraph #2, which doesn't exist
        $expected = ['Paragraph with ID `#1`: choice "Go to #2" points to non-existent `#2` target paragraph'];
        $story->addParagraph(new Paragraph(id: 1, text: 'A paragraph', choices: [new Choice('Go to #2', 2)]));
        $this->assertSame($expected, $story->validate());

        //Paragraph #2 is added, but now there are no errors
        $story->addParagraph(new Paragraph(id: 2, text: 'Another paragraph'));
        $this->assertEmpty($story->validate());
    }

    #[Test]
    public function testToArray(): void
    {
        $expected = [
            'metadata' => [
                'title' => 'A title',
                'author' => 'Mirko',
                'description' => 'A description',
                'initial_gold' => 10,
            ],
            'paragraphs' => [
                1 => ['text' => 'A paragraph'],
                2 => ['text' => 'Another paragraph'],
            ],
        ];

        $this->assertSame($expected, $this->story->toArray());
    }

    #[Test]
    public function testFromArray(): void
    {
        $story = Story::fromArray([
            'metadata' => [
                'title' => 'A title',
                'author' => 'Mirko',
                'description' => 'A description',
                'initial_gold' => 10,
            ],
            'paragraphs' => [
                1 => ['text' => 'A paragraph'],
                2 => ['text' => 'Another paragraph'],
            ],
        ]);

        $this->assertEquals($this->story, $story);
    }

    public static function fromArrayOnErrorDataProvider(): Generator
    {
        yield [
            'Story data missing "metadata"',
            [],
        ];

        yield [
            'Story data missing "metadata"',
            ['metadata' => []],
        ];

        yield [
            'Metadata missing "title"',
            ['metadata' => ['author' => 'Mirko']],
        ];

        yield [
            'Metadata missing "author"',
            ['metadata' => ['title' => 'A title']],
        ];

        yield [
            'Story data missing "paragraphs"',
            [
                'metadata' => ['title' => 'A title', 'author' => 'Mirko'],
                'paragraphs' => [],
            ],
        ];
    }

    /**
     * @param string $expectedExceptionMessage The expected exception message to be triggered.
     * @param array{
     *     metadata: array{title: string, author: string},
     *     paragraphs: array<int, array{text: string}>
     * } $data The input data array used to call the `Story::fromArray` method.
     */
    #[Test]
    #[DataProvider('fromArrayOnErrorDataProvider')]
    public function testFromArrayOnError(string $expectedExceptionMessage, array $data): void
    {
        $this->expectExceptionMessage($expectedExceptionMessage);
        Story::fromArray($data);
    }
}
