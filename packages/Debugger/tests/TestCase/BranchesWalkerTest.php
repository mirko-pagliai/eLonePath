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

        $this->assertSame(['Remaining nodes: 4'], $walker());
    }

    /**
     * @link \Elone\Debugger\BranchesWalker::__invoke()
     */
    #[Test]
    public function testInvokeWithMultipleRemainingNodes(): void
    {
        $game = Game::createFromString('{' . self::GAME_HEADER . ', "nodes": {
            "1": {"content": "start", "type": "passage", "choices": [
                {"content": "win", "target": 2}, {"content": "lose", "target": 3}
            ]},
            "2": {"content": "you win", "type": "victory"},
            "3": {"content": "you lose", "type": "defeat"},
            "4": {"content": "never reached", "type": "victory"},
            "5": {"content": "never reached either", "type": "defeat"}
        }}');

        $walker = new BranchesWalker($game);

        $this->assertSame(['Remaining nodes: 4, 5'], $walker());
    }

    /**
     * Two different choices converging on the same later node (a "diamond" in the story graph — a very normal
     * pattern) each produce their own branch: the second one to arrive still gets recorded, ending at the shared
     * node, rather than being silently dropped as an incomplete path.
     *
     * @link \Elone\Debugger\BranchesWalker::getAllBranches()
     * @link \Elone\Debugger\BranchesWalker::getRemainingNodes()
     */
    #[Test]
    public function testGetAllBranchesWithMergingPathsIntoAnEnding(): void
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
        $this->assertCount(2, $branches);
        $this->assertSame([1, 2, 4], array_map(fn($node) => $node->id, $branches[0]));
        $this->assertSame([1, 3, 4], array_map(fn($node) => $node->id, $branches[1]));

        $this->assertCount(2, $walker->getWinningBranches());
        $this->assertSame([], $walker->getRemainingNodes());
    }

    /**
     * The merge point isn't always an ending: when the shared node is a plain passage further along the way, the
     * branch that arrives second is still recorded, but it ends at that passage — not at whatever ending the first
     * branch eventually reaches past it. `getAllBranches()` can return branches that never reach a `VictoryNode` or
     * `DefeatNode` at all; `getWinningBranches()`/`getDefeatBranches()` are the ones that filter for a genuine
     * ending.
     *
     * @link \Elone\Debugger\BranchesWalker::getAllBranches()
     */
    #[Test]
    public function testGetAllBranchesWithMergingPathsIntoAnIntermediateNode(): void
    {
        $game = Game::createFromString('{' . self::GAME_HEADER . ', "nodes": {
            "1": {"content": "start", "type": "passage", "choices": [
                {"content": "a", "target": 2}, {"content": "b", "target": 3}
            ]},
            "2": {"content": "path a", "type": "passage", "choices": [{"content": "c", "target": 4}]},
            "3": {"content": "path b", "type": "passage", "choices": [{"content": "c", "target": 4}]},
            "4": {"content": "merge point", "type": "passage", "choices": [{"content": "d", "target": 5}]},
            "5": {"content": "the end", "type": "victory"}
        }}');

        $walker = new BranchesWalker($game);

        $branches = $walker->getAllBranches();
        $this->assertCount(2, $branches);
        $this->assertSame([1, 2, 4, 5], array_map(fn($node) => $node->id, $branches[0]));
        $this->assertSame([1, 3, 4], array_map(fn($node) => $node->id, $branches[1]));

        $this->assertCount(1, $walker->getWinningBranches());
    }

    /**
     * Regression test: an accidental cycle in the story data (a choice that loops back to an earlier node — an easy
     * mistake to make by hand in a JSON file) must not recurse forever. The branch that closes the loop is recorded
     * ending at the re-visited node — like any other re-convergence — rather than dropped.
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

        $branches = $walker->getAllBranches();
        $this->assertCount(1, $branches);
        $this->assertSame([1, 2, 1], array_map(fn($node) => $node->id, $branches[0]));
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

    /**
     * A `CombatNode` branches on its own two targets (`target_victory`/`target_defeat`), the same shape as
     * `DiceNode`'s `target_success`/`target_failure` — this is what proves the walker actually knows about it,
     * rather than falling through to the `LogicException` every other unrecognized type hits.
     *
     * @link \Elone\Debugger\BranchesWalker::getAllBranches()
     */
    #[Test]
    public function testGetAllBranchesWithCombatNode(): void
    {
        $game = Game::createFromString('{' . self::GAME_HEADER . ', "nodes": {
            "1": {"content": "start", "type": "passage", "choices": [{"content": "fight", "target": 2}]},
            "2": {"content": "a fight", "type": "combat", "combat": {
                "enemy_name": "Orco", "enemy_max_life_points": 10, "enemy_strength": 5, "enemy_agility": 3,
                "target_victory": 3, "target_defeat": 4
            }},
            "3": {"content": "you win", "type": "victory"},
            "4": {"content": "you lose", "type": "defeat"}
        }}');

        $walker = new BranchesWalker($game);

        $this->assertSame([], $walker());

        $branches = $walker->getAllBranches();
        $this->assertCount(2, $branches);
        $this->assertSame([1, 2, 3], array_map(fn($node) => $node->id, $branches[0]));
        $this->assertSame([1, 2, 4], array_map(fn($node) => $node->id, $branches[1]));
    }
}
