<?php
declare(strict_types=1);

namespace Elone\Debugger;

use App\Story\Game;
use App\Story\Nodes\Choice;
use App\Story\Nodes\CombatNode;
use App\Story\Nodes\DefeatNode;
use App\Story\Nodes\DiceNode;
use App\Story\Nodes\Node;
use App\Story\Nodes\PassageNode;
use App\Story\Nodes\VictoryNode;
use LogicException;

/**
 * Walks a game's node graph from node 1, collecting every branch and every node never reached along the way.
 *
 * A branch is either a complete path from node 1 to a `VictoryNode` or a `DefeatNode`, or a path that stops early
 * because it re-converges with a node some other branch already reached first — in that case, the branch is still
 * recorded (up to and including that shared node), but the walk doesn't continue past it a second time. This means
 * `getAllBranches()` can return branches that don't end in a `VictoryNode`/`DefeatNode` at all: `getWinningBranches()`
 * and `getDefeatBranches()` are the ones that filter for a genuine ending.
 *
 * A node is marked as visited the moment it's entered, before recursing into its targets — this, combined with
 * recording the branch even when it re-converges, is what keeps an accidental cycle in the story data (a choice that
 * loops back to an earlier node) from recursing forever.
 */
class BranchesWalker
{
    /**
     * @var list<list<\App\Story\Nodes\Node>>
     */
    protected array $branches = [];

    /**
     * @var array<int, \App\Story\Nodes\Node>
     */
    protected array $remainingNodes;

    protected bool $hasWalked = false;

    public function __construct(protected Game $game)
    {
        $this->remainingNodes = $this->game->nodes;
    }

    /**
     * Runs the walk and returns one message per problem found: no winning branch, no defeat branch, or nodes never
     * reached from node 1.
     *
     * @return list<string>
     */
    public function __invoke(): array
    {
        $errors = [];

        if (!$this->getWinningBranches()) {
            $errors[] = 'No winning branches found';
        }

        if (!$this->getDefeatBranches()) {
            $errors[] = 'No defeat branches found';
        }

        if ($this->getRemainingNodes()) {
            $errors[] = 'Remaining nodes: ' . implode(', ', array_map(
                callback: fn(Node $node): int => $node->id,
                array: $this->getRemainingNodes(),
            ));
        }

        return $errors;
    }

    /**
     * Every branch found by the walk — see the class docblock for what "branch" means here, including the
     * re-convergence case.
     *
     * @return list<list<\App\Story\Nodes\Node>>
     */
    public function getAllBranches(): array
    {
        $this->walk();

        return $this->branches;
    }

    /**
     * Every branch that ends in a `VictoryNode`.
     *
     * @return list<list<\App\Story\Nodes\Node>>
     */
    public function getWinningBranches(): array
    {
        return array_values(array_filter(
            array: $this->getAllBranches(),
            callback: fn(array $branch): bool => end($branch) instanceof VictoryNode,
        ));
    }

    /**
     * Every branch that ends in a `DefeatNode`.
     *
     * @return list<list<\App\Story\Nodes\Node>>
     */
    public function getDefeatBranches(): array
    {
        return array_values(array_filter(
            array: $this->getAllBranches(),
            callback: fn(array $branch): bool => end($branch) instanceof DefeatNode,
        ));
    }

    /**
     * Every node never reached by the walk from node 1.
     *
     * @return array<int, \App\Story\Nodes\Node>
     */
    public function getRemainingNodes(): array
    {
        $this->walk();

        return $this->remainingNodes;
    }

    /**
     * Runs the walk exactly once; however, many of the getters above are called.
     */
    protected function walk(): void
    {
        if ($this->hasWalked) {
            return;
        }

        $this->hasWalked = true;

        $this->scanNode($this->game->getNode(1), []);
    }

    /**
     * @param list<\App\Story\Nodes\Node> $branch The branch accumulated so far, up to (but not including) `$node`.
     * @throws \LogicException If `$node` is neither a `VictoryNode`, `DefeatNode`, `DiceNode`, `CombatNode`, nor
     * `PassageNode`.
     */
    protected function scanNode(Node $node, array $branch): void
    {
        $branch[] = $node;

        /**
         * This node has already been visited via another branch, so this branch is recorded as-is, ending here, and
         * the walk doesn't continue past it a second time.
         */
        if (!isset($this->remainingNodes[$node->id])) {
            $this->branches[] = $branch;

            return;
        }

        // Marked as visited immediately, before recursing into targets — this is what makes cycles safe.
        unset($this->remainingNodes[$node->id]);

        if ($node instanceof VictoryNode || $node instanceof DefeatNode) {
            $this->branches[] = $branch;

            return;
        }

        if ($node instanceof DiceNode) {
            $targetNodes = [
                $this->game->getNode($node->targetSuccess),
                $this->game->getNode($node->targetFailure),
            ];
        } elseif ($node instanceof CombatNode) {
            $targetNodes = [
                $this->game->getNode($node->targetVictory),
                $this->game->getNode($node->targetDefeat),
            ];
        } elseif ($node instanceof PassageNode) {
            $targetNodes = array_map(
                callback: fn(Choice $choice): Node => $this->game->getNode($choice->target),
                array: $node->choices,
            );
        } else {
            throw new LogicException("Node `$node->id` has an unexpected type.");
        }

        foreach ($targetNodes as $targetNode) {
            $this->scanNode($targetNode, $branch);
        }
    }
}
