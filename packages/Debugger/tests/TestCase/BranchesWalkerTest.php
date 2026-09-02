<?php
declare(strict_types=1);

namespace Elone\Debugger\Test;

use App\Story\Game;
use Elone\Debugger\BranchesWalker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * BranchesWalkerTest.
 */
#[CoversClass(BranchesWalker::class)]
class BranchesWalkerTest extends TestCase
{
    /**
     * A minimal but complete game header, reused across the story JSON strings below.
     */
    private const GAME_HEADER = '"game": {
        "id": "test-game", "title": "t", "author": "a", "description": "d", "language": "it", "version": "1.0"
    }';

    /**
     * @link \Elone\Debugger\BranchesWalker::__invoke()
     */
    #[Test]
    public function testInvokeWithValidStory(): void
    {
        $game = Game::createFromString('{' . self::GAME_HEADER . ', "nodes": {
            "1": {"content": "start", "type": "passage", "choices": [
                {"content": "win", "target": 2}, {"content": "lose", "target": 3}
            ]},
            "2": {"content": "you win", "type": "victory"},
            "3": {"content": "you lose", "type": "defeat"}
        }}');

        $walker = new BranchesWalker($game);

        $this->assertSame([], $walker());
    }

    /**
     * @link \Elone\Debugger\BranchesWalker::__invoke()
     */
    #[Test]
    public function testInvokeWithNoWinningBranch(): void
    {
        $game = Game::createFromString('{' . self::GAME_HEADER . ', "nodes": {
            "1": {"content": "start", "type": "passage", "choices": [{"content": "lose", "target": 2}]},
            "2": {"content": "you lose", "type": "defeat"}
        }}');

        $walker = new BranchesWalker($game);

        $this->assertSame(['No winning branches found'], $walker());
    }

    /**
     * @link \Elone\Debugger\BranchesWalker::__invoke()
     */
    #[Test]
    public function testInvokeWithNoDefeatBranch(): void
    {
        $game = Game::createFromString('{' . self::GAME_HEADER . ', "nodes": {
            "1": {"content": "start", "type": "passage", "choices": [{"content": "win", "target": 2}]},
            "2": {"content": "you win", "type": "victory"}
        }}');

        $walker = new BranchesWalker($game);

        $this->assertSame(['No defeat branches found'], $walker());
    }

    /**
     * @link \Elone\Debugger\BranchesWalker::__invoke()
     */
    #[Test]
    public function testInvokeWithRemainingNode(): void
    {
        $game = Game::createFromString('{' . self::GAME_HEADER . ', "nodes": {
            "1": {"content": "start", "type": "passage", "choices": [
                {"content": "win", "target": 2}, {"content": "lose", "target": 3}
            ]},
            "2": {"content": "you win", "type": "victory"},
            "3": {"content": "you lose", "type": "defeat"},
            "4": {"content": "never reached", "type": "victory"}
        }}');

        $walker = new BranchesWalker($game);

        $this->assertSame(['Remaining node: 4'], $walker());
    }

    /**
     * Regression test: a node reached by two different choices (a "diamond" in the story graph — a very normal
     * pattern, two different earlier choices converging on the same later page) must not be recorded as a second,
     * truncated branch, and every node involved must still count as reached.
     *
     * @link \Elone\Debugger\BranchesWalker::getAllBranches()
     * @link \Elone\Debugger\BranchesWalker::getRemainingNodes()
     */
    #[Test]
    public function testGetAllBranchesWithMergingPaths(): void
    {
        $game = Game::createFromString('{' . self::GAME_HEADER . ', "nodes": {
            "1": {"content": "start", "type": "passage", "choices": [
                {"content": "a", "target": 2}, {"content": "b", "target": 3}
            ]},
            "2": {"content": "path a", "type": "passage", "choices": [{"content": "c", "target": 4}]},
            "3": {"content": "path b", "type": "passage", "choices": [{"content": "c", "target": 4}]},
            "4": {"content": "merge", "type": "victory"}
        }}');

        $walker = new BranchesWalker($game);

        $branches = $walker->getAllBranches();
        $this->assertCount(1, $branches);
        $this->assertSame([1, 2, 4], array_map(fn($node) => $node->id, $branches[0]));

        $this->assertSame([], $walker->getRemainingNodes());
    }

    /**
     * Regression test: an accidental cycle in the story data (a choice that loops back to an earlier node — an easy
     * mistake to make by hand in a JSON file) must not recurse forever.
     *
     * @link \Elone\Debugger\BranchesWalker::__invoke()
     */
    #[Test]
    public function testInvokeWithCycle(): void
    {
        $game = Game::createFromString('{' . self::GAME_HEADER . ', "nodes": {
            "1": {"content": "start", "type": "passage", "choices": [{"content": "a", "target": 2}]},
            "2": {"content": "loop", "type": "passage", "choices": [{"content": "back", "target": 1}]}
        }}');

        $walker = new BranchesWalker($game);

        $this->assertSame(['No winning branches found', 'No defeat branches found'], $walker());
        $this->assertSame([], $walker->getAllBranches());
    }

    /**
     * @link \Elone\Debugger\BranchesWalker::getWinningBranches()
     * @link \Elone\Debugger\BranchesWalker::getDefeatBranches()
     */
    #[Test]
    public function testGetWinningAndDefeatBranches(): void
    {
        $game = Game::createFromString('{' . self::GAME_HEADER . ', "nodes": {
            "1": {"content": "start", "type": "passage", "choices": [
                {"content": "win", "target": 2}, {"content": "lose", "target": 3}
            ]},
            "2": {"content": "you win", "type": "victory"},
            "3": {"content": "you lose", "type": "defeat"}
        }}');

        $walker = new BranchesWalker($game);

        $this->assertCount(1, $walker->getWinningBranches());
        $this->assertCount(1, $walker->getDefeatBranches());
    }
}
