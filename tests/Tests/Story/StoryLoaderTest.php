<?php
declare(strict_types=1);

namespace eLonePath\Test\Story;

use eLonePath\Story\Choice;
use eLonePath\Story\Paragraph;
use eLonePath\Story\StoryLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StoryLoader::class)]
class StoryLoaderTest extends TestCase
{
    #[Test]
    public function testConstruct(): void
    {
        // Adds the directory separator. However, it is expected to be removed
        $loader = new class (RESOURCES . DS . 'stories' . DS) extends StoryLoader {
            public function getStoriesDirectory(): string
            {
                return $this->storiesDirectory;
            }
        };

        $this->assertSame(RESOURCES . DS . 'stories', $loader->getStoriesDirectory());
    }

    #[Test]
    public function testGetStoryPath(): void
    {
        $loader = new class(RESOURCES . DS . 'stories') extends StoryLoader {
            public function getStoryPath(string $storyId): string
            {
                return parent::getStoryPath($storyId);
            }
        };
        $this->assertSame(RESOURCES . DS . 'stories' . DS . 'cave_of_trials', $loader->getStoryPath('cave_of_trials'));
    }

    #[Test]
    public function testGetMetadata(): void
    {
        $expected = [
            'title' => 'The Cave of Trials',
            'author' => 'Demo Author',
            'description' => 'A short test adventure to demonstrate game mechanics',
            'initial_gold' => 10,
        ];

        $loader = new class(RESOURCES . DS . 'stories') extends StoryLoader {
            public function getMetadata(string $storyId): array
            {
                return parent::getMetadata($storyId);
            }
        };

        $result = $loader->getMetadata('cave_of_trials');
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function testGetParagraphs(): void
    {
        $loader = new class(RESOURCES . DS . 'stories') extends StoryLoader {
            public function getParagraphs(string $storyId): array
            {
                return parent::getParagraphs($storyId);
            }
        };

        $paragraphs = $loader->getParagraphs('cave_of_trials');
        $this->assertGreaterThan(1, count($paragraphs));
        foreach ($paragraphs as $paragraph) {
            $this->assertArrayHasKey('text', $paragraph);
            $this->assertArrayHasKey('choices', $paragraph);
        }
    }

    #[Test]
    public function testLoad(): void
    {
        $loader = new StoryLoader(RESOURCES . DS . 'stories');
        $loadedStory = $loader->load('cave_of_trials');

        $this->assertSame('The Cave of Trials', $loadedStory->title);
        $this->assertSame('Demo Author', $loadedStory->author);
        $this->assertSame('A short test adventure to demonstrate game mechanics', $loadedStory->description);
        $this->assertSame(10, $loadedStory->initialGold);
    }

    #[Test]
    public function testLoadOnValidationError(): void
    {
        $dir = RESOURCES . DS . 'stories' . DS . 'cave_of_trials';

        $expectedExceptionMessage = <<<MESSAGE
Story validation failed in `{$dir}`:
- Story must have a starting paragraph with ID `#1`
- Paragraph with ID `#2`: choice "Go to #3" points to non-existent `#3` target paragraph
MESSAGE;

        $loader = $this->getMockBuilder(StoryLoader::class)
            ->setConstructorArgs([RESOURCES . DS . 'stories'])
            ->onlyMethods(['getParagraphs'])
            ->getMock();

        $loader
            ->expects($this->once())
            ->method('getParagraphs')
            ->with('cave_of_trials')
            ->willReturn([
                2 => new Paragraph(id: 2, text: 'A paragraph', choices: [new Choice('Go to #3', 3)])->toArray(),
            ]);

        $this->expectExceptionMessage($expectedExceptionMessage);
        $loader->load('cave_of_trials');
    }

    #[Test]
    public function testListAvailableStories(): void
    {
        $expected = [
            'cave_of_trials' => [
                'title' => 'The Cave of Trials',
                'author' => 'Demo Author',
                'description' => 'A short test adventure to demonstrate game mechanics',
            ],
        ];
        $loader = new StoryLoader(RESOURCES . DS . 'stories');
        $stories = $loader->listAvailableStories();
        $this->assertSame($expected, $stories);
    }

    #[Test]
    public function testListAvailableStoriesWithNoStoriesOnDirectory(): void
    {
        $this->expectExceptionMessage('No stories found in `' . dirname(__FILE__) . '` directory.');
        $loader = new StoryLoader(dirname(__FILE__));
        $loader->listAvailableStories();
    }
}
