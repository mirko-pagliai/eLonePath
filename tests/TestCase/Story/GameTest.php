<?php
declare(strict_types=1);

namespace Test\Story;

use App\Story\Game;
use App\Story\Nodes\Node;
use Elone\Core\Exception\HttpException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * GameTest.
 */
#[CoversClass(Game::class)]
class GameTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function sampleData(): array
    {
        return [
            'game' => [
                'id' => 'test-game',
                'title' => 'Test Game',
                'author' => 'Test Author',
                'translators' => '',
                'description' => 'A game for testing.',
                'language' => 'it',
                'version' => '1.0',
                'preface' => 'A short preface for testing.',
            ],
            'nodes' => [
                1 => [
                    'content' => 'Start here.',
                    'choices' => [
                        ['content' => 'Go to page {{page}}', 'target' => 2],
                    ],
                    'type' => 'passage',
                ],
                2 => [
                    'content' => 'The end.',
                    'type' => 'victory',
                ],
            ],
        ];
    }

    /**
     * @link \App\Story\Game::createFromArray()
     */
    #[Test]
    public function testCreateFromArray(): void
    {
        $game = Game::createFromArray($this->sampleData());

        $this->assertSame('test-game', $game->gameId);
        $this->assertSame('Test Game', $game->title);
        $this->assertSame('Test Author', $game->author);
        $this->assertSame('', $game->translators);
        $this->assertSame('A game for testing.', $game->description);
        $this->assertSame('it', $game->language);
        $this->assertSame('1.0', $game->version);
        $this->assertSame('A short preface for testing.', $game->preface);
    }

    /**
     * `sampleData()` never sets `requires_combat` — this is what every existing story, written before the key
     * existed, gets: no character required, exactly like before.
     *
     * @link \App\Story\Game::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayDefaultsRequiresCombatToFalse(): void
    {
        $game = Game::createFromArray($this->sampleData());

        $this->assertFalse($game->requiresCombat);
    }

    /**
     * @link \App\Story\Game::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayWithRequiresCombatTrue(): void
    {
        $data = $this->sampleData();
        $data['game']['requires_combat'] = true;

        $game = Game::createFromArray($data);

        $this->assertTrue($game->requiresCombat);
    }

    /**
     * @link \App\Story\Game::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayWithoutOptionalKeys(): void
    {
        $data = $this->sampleData();
        unset($data['game']['translators'], $data['game']['preface']);

        $game = Game::createFromArray($data);

        $this->assertSame('', $game->translators);
        $this->assertSame('', $game->preface);
    }

    /**
     * @link \App\Story\Game::getNode()
     */
    #[Test]
    public function testGetNode(): void
    {
        $game = Game::createFromArray($this->sampleData());

        $node = $game->getNode(1);

        $this->assertInstanceOf(Node::class, $node);
        $this->assertSame(1, $node->id);
        $this->assertStringContainsString('Start here.', $node->content);
    }

    /**
     * @link \App\Story\Game::getNode()
     */
    #[Test]
    public function testGetNodeWithMissingNode(): void
    {
        $game = Game::createFromArray($this->sampleData());

        try {
            $game->getNode(99);
            $this->fail('Expected an HttpException to be thrown.');
        } catch (HttpException $exception) {
            $this->assertSame('Node `99` not found in `test-game`.', $exception->getMessage());
            $this->assertSame(404, $exception->statusCode());
        }
    }

    /**
     * `sampleData()` deliberately omits the optional `image` key on its nodes and doesn't order keys the way
     * `toArray()` does — this is the complete shape `toArray()` actually produces, `image: null` and all, used to
     * verify the round trip precisely rather than comparing against `sampleData()` itself.
     *
     * @link \App\Story\Game::toArray()
     */
    #[Test]
    public function testToArray(): void
    {
        $game = Game::createFromArray($this->sampleData());

        $result = $game->toArray();

        $this->assertSame([
            'game' => [
                'id' => 'test-game',
                'title' => 'Test Game',
                'author' => 'Test Author',
                'translators' => '',
                'description' => 'A game for testing.',
                'language' => 'it',
                'version' => '1.0',
                'preface' => 'A short preface for testing.',
                'requires_combat' => false,
            ],
            'nodes' => [
                1 => [
                    'content' => 'Start here.',
                    'image' => null,
                    'type' => 'passage',
                    'choices' => [
                        ['content' => 'Go to page 2', 'target' => 2],
                    ],
                ],
                2 => [
                    'content' => 'The end.',
                    'image' => null,
                    'type' => 'victory',
                ],
            ],
        ], $result);
    }

    /**
     * @link \App\Story\Game::createFromFile()
     */
    #[Test]
    public function testCreateFromFile(): void
    {
        $game = Game::createFromFile(STORIES . '/valid/story.json');

        $this->assertSame('test-game', $game->gameId);
        $this->assertInstanceOf(Node::class, $game->getNode(2));
    }

    /**
     * Test for the `createFromFile()` method with a non-readable file.
     *
     * @link \App\Story\Game::createFromFile()
     */
    #[Test]
    public function testCreateFromFileWithNotReadableFile(): void
    {
        $this->expectExceptionMessageIs('Failed to read `/path/does/not/story.json`.');
        Game::createFromFile('/path/does/not/story.json');
    }

    /**
     * Test for the `createFromFile()` method with a file not named `story.json`.
     *
     * @link \App\Story\Game::createFromFile()
     */
    #[Test]
    public function testCreateFromFileInvalidStoryJsonFile(): void
    {
        $path = STORIES . '/invalid-name/data.json';

        $this->expectExceptionMessageIs("Expected `$path` to be a `story.json` file.");
        Game::createFromFile($path);
    }

    /**
     * Test for the `createFromFile()` method with a JSON file that cannot be parsed.
     *
     * @link \App\Story\Game::createFromFile()
     */
    #[Test]
    public function testCreateFromFileFailedToParseJson(): void
    {
        $path = STORIES . '/invalid-json/story.json';

        $this->expectExceptionMessageMatches('#^Failed to parse `' . preg_quote($path) . '`: .+\.$#');
        Game::createFromFile($path);
    }

    /**
     * Test for the `createFromFile()` method with a JSON file that does not have the expected shape.
     *
     * @link \App\Story\Game::createFromFile()
     */
    #[Test]
    public function testCreateFromFileFailedToParseJsonDueToWrongShape(): void
    {
        $path = STORIES . '/wrong-shape/story.json';

        $this->expectExceptionMessageIs("Failed to parse `$path`: expected a JSON object at the top level.");
        Game::createFromFile($path);
    }
}
