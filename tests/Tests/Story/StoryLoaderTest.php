<?php
declare(strict_types=1);

namespace eLonePath\Test\Story;

use eLonePath\Story\Choice;
use eLonePath\Story\Paragraph;
use eLonePath\Story\Story;
use eLonePath\Story\StoryLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StoryLoader::class)]
class StoryLoaderTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        @mkdir(TMP . DS . 'stories' . DS . 'my-story', 0777, true);
    }

    #[Test]
    public function testConstruct(): void
    {
        // Adds the directory separator. However, it is expected to be removed
        $dir = TMP . DS . 'stories' . DS . 'my-story' . DS;

        $loader = new class ($dir) extends StoryLoader {
            public function getStoriesDirectory(): string
            {
                return $this->storiesDirectory;
            }
        };

        $this->assertSame(rtrim($dir, DS), $loader->getStoriesDirectory());
    }

    #[Test]
    public function testLoad(): void
    {
        $dir = TMP . DS . 'stories' . DS . 'my-story';

        $story = new Story('A title', 'Mirko', 'A description', 10)
            ->addParagraph(new Paragraph(1, 'A paragraph'))
            ->addParagraph(new Paragraph(2, 'Another paragraph'));

        //Writes the story as arrays to temporary files
        file_put_contents($dir . DS . 'metadata.json', json_encode($story->toArray()['metadata']));
        file_put_contents($dir . DS . 'en.json', json_encode(['paragraphs' => $story->toArray()['paragraphs']]));

        $loader = new StoryLoader(dirname($dir));
        $loadedStory = $loader->load(basename($dir));

        unlink($dir . DS . 'metadata.json');
        unlink($dir . DS . 'en.json');

        $this->assertEquals($story, $loadedStory);
    }

    #[Test]
    public function testLoadOnValidationError(): void
    {
        $dir = TMP . DS . 'stories' . DS . 'my-story';

        $expectedExceptionMessage = <<<MESSAGE
Story validation failed in `{$dir}`:
- Story must have a starting paragraph with ID `#1`
- Paragraph with ID `#2`: choice "Go to #3" points to non-existent `#3` target paragraph
MESSAGE;

        //This story does not have paragraph #1. Also, paragraph #2 points to paragraph #3, which doesn't exist
        $story = new Story('A title', 'Mirko', 'A description', 10)
            ->addParagraph(new Paragraph(id: 2, text: 'A paragraph', choices: [new Choice('Go to #3', 3)]));

        //Writes the story as arrays to temporary files
        file_put_contents($dir . DS . 'metadata.json', json_encode($story->toArray()['metadata']));
        file_put_contents($dir . DS . 'en.json', json_encode(['paragraphs' => $story->toArray()['paragraphs']]));

        $this->expectExceptionMessage($expectedExceptionMessage);
        $loader = new StoryLoader(dirname($dir));
        $loader->load(basename($dir));

        unlink($dir . DS . 'metadata.json');
        unlink($dir . DS . 'en.json');
    }

    #[Test]
    public function testListAvailableStories(): void
    {
        $loader = new StoryLoader(TESTS_RESOURCES . DS . 'stories');
        $stories = $loader->listAvailableStories();
        $this->assertSame(['cave_of_trials'], $stories);

        $loader = new StoryLoader(dirname(__FILE__));
        $stories = $loader->listAvailableStories();
        $this->assertEmpty($stories);
    }

    #[Test]
    public function testExists(): void
    {
        $loader = new StoryLoader(TESTS_RESOURCES . DS . 'stories');
        $this->assertTrue($loader->exists('cave_of_trials'));
        $this->assertFalse($loader->exists('no_existing_story'));
    }
}
