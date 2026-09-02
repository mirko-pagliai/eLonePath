<?php
declare(strict_types=1);

namespace Elone\Debugger;

use App\Story\Game;
use App\Story\Nodes\Choice;
use App\Story\Nodes\DefeatNode;
use App\Story\Nodes\DiceNode;
use App\Story\Nodes\Node;
use App\Story\Nodes\PassageNode;
use App\Story\Nodes\VictoryNode;
use LogicException;

/**
 * Walks a game's node graph from node 1, collecting every complete branch (a path ending in a `VictoryNode` or a
 * `DefeatNode`) and every node never reached along the way.
 *
 * A node is marked as visited the moment it's entered, before recursing into its targets. This is what keeps the
 * walk correct when two different choices lead to the same later node — the second branch simply stops there
 * instead of being recorded as an incomplete, dead-end branch — and safe against an accidental cycle in the story
 * data (a choice that loops back to an earlier node), which would otherwise recurse forever.
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
     * Runs the walk and returns one message per problem found: no winning branch, no defeat branch, or a node
     * never reached from node 1.
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
     * Every complete branch from node 1 to a `VictoryNode` or a `DefeatNode`.
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
     * @throws \LogicException If `$node` is neither a `VictoryNode`, `DefeatNode`, `DiceNode`, nor `PassageNode`.
     */
    protected function scanNode(Node $node, array $branch): void
    {
        // Already reached via another branch: this branch merges here, nothing more to record.
        if (!isset($this->remainingNodes[$node->id])) {
            return;
        }

        // Marked as visited immediately, before recursing into targets — this is what makes merges and cycles safe.
        unset($this->remainingNodes[$node->id]);

        $branch[] = $node;

        if ($node instanceof VictoryNode || $node instanceof DefeatNode) {
            $this->branches[] = $branch;

            return;
        }

        if ($node instanceof DiceNode) {
            $targetNodes = [
                $this->game->getNode($node->targetSuccess),
                $this->game->getNode($node->targetFailure),
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
