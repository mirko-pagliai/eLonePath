<?php
declare(strict_types=1);

namespace Test\Story;

use App\Story\Game;
use App\Story\Nodes\Node;
use Elone\Core\Exception\HttpException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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
        $this->assertStringContainsString('<p>Start here.</p>', $node->content);
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
     * @link \App\Story\Game::createFromFile()
     */
    #[Test]
    public function testCreateFromFile(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'story');
        file_put_contents($path, json_encode($this->sampleData()));

        try {
            $game = Game::createFromFile($path);

            $this->assertSame('test-game', $game->gameId);
            $this->assertInstanceOf(Node::class, $game->getNode(2));
        } finally {
            unlink($path);
        }
    }

    /**
     * @link \App\Story\Game::createFromFile()
     */
    #[Test]
    public function testCreateFromFileWithMissingFile(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Failed to read `/path/does/not/exist.json`.');
        Game::createFromFile('/path/does/not/exist.json');
    }

    /**
     * @link \App\Story\Game::createFromFile()
     */
    #[Test]
    public function testCreateFromFileWithInvalidJson(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'story');
        file_put_contents($path, 'not valid json');

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessageMatches('/^Failed to parse `' . preg_quote($path, '/') . '`: .+\.$/');
            Game::createFromFile($path);
        } finally {
            unlink($path);
        }
    }

    /**
     * @link \App\Story\Game::createFromFile()
     */
    #[Test]
    public function testCreateFromFileWithValidJsonButWrongShape(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'story');
        file_put_contents($path, '"just a string"');

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessageIs("Failed to parse `$path`: expected a JSON object at the top level.");
            Game::createFromFile($path);
        } finally {
            unlink($path);
        }
    }
}
