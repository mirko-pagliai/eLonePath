<?php
declare(strict_types=1);

namespace Elone\Debugger\Test;

use App\Story\Game;
use Elone\Debugger\NodeImagesWalker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * NodeImagesWalkerTest.
 */
#[CoversClass(NodeImagesWalker::class)]
class NodeImagesWalkerTest extends TestCase
{
    /**
     * A minimal but complete game header, reused across the story JSON strings below.
     */
    private const GAME_HEADER = '"game": {
        "id": "test-game", "title": "t", "author": "a", "description": "d", "language": "it", "version": "1.0"
    }';

    /**
     * Builds a single-node `Game` whose one node's `content` starts with a leading image, resolved against
     * `STORIES . '/test-game/img/'` by `NodeImagesWalker`.
     */
    private function gameWithNodeImage(string $path, string $alt = 'An illustration'): Game
    {
        return Game::createFromString('{' . self::GAME_HEADER . ', "nodes": {
            "1": {"content": "![' . $alt . '](' . $path . ')\nSome text.", "type": "victory"}
        }}');
    }

    /**
     * @link \Elone\Debugger\NodeImagesWalker::__invoke()
     */
    #[Test]
    public function testInvokeWithValidImage(): void
    {
        $walker = new NodeImagesWalker($this->gameWithNodeImage('correct_960x600.jpg'));

        $this->assertSame([], $walker());
    }

    /**
     * A genuinely empty file is checked for explicitly, before `getimagesize()` — calling that on a 0-byte file
     * triggers a PHP notice as a side effect, which this avoids by never making the call in the first place.
     *
     * @link \Elone\Debugger\NodeImagesWalker::__invoke()
     */
    #[Test]
    public function testInvokeWithEmptyFile(): void
    {
        $walker = new NodeImagesWalker($this->gameWithNodeImage('not-an-image.txt'));

        $errors = $walker();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('is an empty file', $errors[0]);
    }

    /**
     * A non-empty file that still isn't a valid image — distinct from the empty-file case above, which is caught
     * earlier and never reaches this check.
     *
     * @link \Elone\Debugger\NodeImagesWalker::__invoke()
     */
    #[Test]
    public function testInvokeWithNonEmptyNonImageFile(): void
    {
        $walker = new NodeImagesWalker($this->gameWithNodeImage('wrong-content.txt'));

        $errors = $walker();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('is not a valid jpeg file', $errors[0]);
    }

    /**
     * @link \Elone\Debugger\NodeImagesWalker::__invoke()
     */
    #[Test]
    #[TestWith(['wrong-format.png'])]
    #[TestWith(['wrong-format.gif'])]
    public function testInvokeWithWrongFormat(string $path): void
    {
        $walker = new NodeImagesWalker($this->gameWithNodeImage($path));

        $errors = $walker();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('is not a valid jpeg file', $errors[0]);
    }

    /**
     * The width check accepts only exactly 960px — a mismatch in either direction is the same failure.
     *
     * @link \Elone\Debugger\NodeImagesWalker::__invoke()
     */
    #[Test]
    public function testInvokeWithWrongWidth(): void
    {
        $walker = new NodeImagesWalker($this->gameWithNodeImage('wrong-width_700x600.jpg'));

        $errors = $walker();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('is not 960px wide', $errors[0]);
    }

    /**
     * @link \Elone\Debugger\NodeImagesWalker::__invoke()
     */
    #[Test]
    public function testInvokeWithTooTall(): void
    {
        $walker = new NodeImagesWalker($this->gameWithNodeImage('too-tall_960x1200.jpg'));

        $errors = $walker();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('is greater than 960px high', $errors[0]);
    }

    /**
     * Unlike width, height only has a ceiling — anything under 960px is explicitly allowed. This locks that
     * asymmetry in, rather than leaving it provable only by reading the code.
     *
     * @link \Elone\Debugger\NodeImagesWalker::__invoke()
     */
    #[Test]
    public function testInvokeWithShorterImage(): void
    {
        $walker = new NodeImagesWalker($this->gameWithNodeImage('short_960x400.jpg'));

        $this->assertSame([], $walker());
    }

    /**
     * @link \Elone\Debugger\NodeImagesWalker::__invoke()
     */
    #[Test]
    public function testInvokeWithMissingFile(): void
    {
        $walker = new NodeImagesWalker($this->gameWithNodeImage('does-not-exist.jpg'));

        $errors = $walker();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('is not readable', $errors[0]);
    }

    /**
     * @link \Elone\Debugger\NodeImagesWalker::__invoke()
     */
    #[Test]
    public function testInvokeWithEmptyAltText(): void
    {
        $walker = new NodeImagesWalker($this->gameWithNodeImage('correct_960x600.jpg', alt: ''));

        $errors = $walker();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('alt text for node 1 is empty', $errors[0]);
    }

    /**
     * @link \Elone\Debugger\NodeImagesWalker::getAllNodesWithImages()
     */
    #[Test]
    public function testGetAllNodesWithImagesSkipsNodesWithoutImages(): void
    {
        $game = Game::createFromString('{' . self::GAME_HEADER . ', "nodes": {
            "1": {"content": "no image here", "type": "passage", "choices": [{"content": "go", "target": 2}]},
            "2": {"content": "![An illustration](correct_960x600.jpg)\nSome text.", "type": "victory"}
        }}');

        $walker = new NodeImagesWalker($game);

        $this->assertSame([2], array_keys($walker->getAllNodesWithImages()));
    }
}
