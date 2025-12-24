<?php
declare(strict_types=1);

namespace eLonePath\Test\Story;

use eLonePath\Story\Choice;
use eLonePath\Story\Paragraph;
use eLonePath\Story\Story;
use eLonePath\Story\StoryLoader;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StoryLoader::class)]
class StoryLoaderTest extends TestCase
{
    #[Test]
    public function testConstruct(): void
    {
        // Adds the directory separator if not present. However, it is expected to be removed
        $expected = rtrim(sys_get_temp_dir(), DS);
        $tmp = rtrim(sys_get_temp_dir(), DS) . DS;

        $loader = new class ($tmp) extends StoryLoader {
            public function getStoriesDirectory(): string
            {
                return $this->storiesDirectory;
            }
        };

        $this->assertSame($expected, $loader->getStoriesDirectory());
    }

    public static function dataProviderConstructOnError(): Generator
    {
        yield [
            'File or directory `' . DS . 'noExistingDir` does not exist.',
            DS . 'noExistingDir',
        ];

        yield [
            'File or directory `' . __FILE__ . '` is not a directory.',
            __FILE__,
        ];
    }

    #[Test]
    #[DataProvider('dataProviderConstructOnError')]
    public function testConstructOnError(string $expectedExceptionMessage, string $badPath): void
    {
        $this->expectExceptionMessage($expectedExceptionMessage);
        new StoryLoader($badPath);
    }

    #[Test]
    public function testLoad(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'story');

        $story = new Story('A title', 'Mirko', 'A description', 10);
        $story->addParagraph(new Paragraph(1, 'A paragraph'));
        $story->addParagraph(new Paragraph(2, 'Another paragraph'));

        //Writes the story as an array to a temporary file
        file_put_contents($tmpFile, json_encode($story->toArray()));

        $loader = new StoryLoader(sys_get_temp_dir());
        $loadedStory = $loader->load(basename($tmpFile));

        unlink($tmpFile);

        $this->assertEquals($story, $loadedStory);
    }

    #[Test]
    public function testLoadOnValidationError(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'story');

        $expectedExceptionMessage = <<<MESSAGE
Story validation failed in `{$tmpFile}`:
- Story must have a starting paragraph with ID `#1`
- Paragraph with ID `#2`: choice "Go to #3" points to non-existent `#3` target paragraph
MESSAGE;

        //This story does not have paragraph #1. Also, paragraph #2 points to paragraph #3, which doesn't exist
        $story = new Story('A title', 'Mirko', 'A description', 10);
        $story->addParagraph(new Paragraph(id: 2, text: 'A paragraph', choices: [new Choice('Go to #3', 3)]));

        //Writes the story as an array to a temporary file
        file_put_contents($tmpFile, json_encode($story->toArray()));

        $this->expectExceptionMessage($expectedExceptionMessage);
        $loader = new StoryLoader(sys_get_temp_dir());
        $loader->load(basename($tmpFile));

        unlink($tmpFile);
    }

    #[Test]
    public function testLoadWithNoExistingFile(): void
    {
        $this->expectExceptionMessageMatches('/^File or directory `[\w\/]+noExistingFile\.json` does not exist or is not readable\.$/');
        $loader = new StoryLoader(sys_get_temp_dir());
        $loader->load('noExistingFile.json');
    }

    #[Test]
    public function testLoadWithNoJsonFile(): void
    {
        $this->expectExceptionMessage('Failed to parse JSON in `' . __FILE__ . '` story file');
        $loader = new StoryLoader(dirname(__FILE__));
        $loader->load(basename(__FILE__));
    }

    #[Test]
    public function testListAvailableStories(): void
    {
        $loader = new StoryLoader(TESTS_RESOURCES);
        $stories = $loader->listAvailableStories();
        $this->assertSame(['demo_story.json'], $stories);

        $loader = new StoryLoader(dirname(__FILE__));
        $stories = $loader->listAvailableStories();
        $this->assertEmpty($stories);
    }

    #[Test]
    public function testExists(): void
    {
        $loader = new StoryLoader(TESTS_RESOURCES);
        $this->assertTrue($loader->exists('demo_story.json'));
        $this->assertFalse($loader->exists('noExistingFile.json'));
    }
}
