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

class BranchesWalker
{
    private array $cacheBranches = [];

    /**
     * @var array<\App\Story\Nodes\Node>
     */
    private array $remainingNodes;

    public function __construct(protected Game $game)
    {
        $this->remainingNodes = $this->game->nodes;
    }

    public function __invoke(): array
    {
        $errors = [];

        if ($this->getWinningBranches() < 0) {
            $errors[] = 'No winning branches found';
        }

        if ($this->getDefeatBranches() < 0) {
            $errors[] = 'No defeat branches found';
        }

        foreach ($this->getRemainingNodes() as $node) {
            $errors[] = "Remaining node: $node->id";
        }

        return $errors;
    }

    public function getDefeatBranches(): array
    {
        return array_filter(
            array: $this->getAllBranches(),
            callback: fn($branch): bool => array_last($branch) instanceof DefeatNode,
        );
    }

    /**
     * @return array<\App\Story\Nodes\Node>
     */
    public function getRemainingNodes(): array
    {
        return $this->remainingNodes;
    }

    public function getWinningBranches(): array
    {
        return array_filter(
            array: $this->getAllBranches(),
            callback: fn($branch): bool => array_last($branch) instanceof VictoryNode,
        );
    }

    public function getAllBranches(): array
    {
        if ($this->cacheBranches) {
            return $this->cacheBranches;
        }

        $branches = [];

        $this->scanNode(node: $this->game->getNode(1), branch: [], branches: $branches);

        $this->cacheBranches = $branches;

        return $this->cacheBranches;
    }

    protected function scanNode(Node $node, array $branch, array &$branches): void
    {
        $branch[] = $node;

        // This node has already been inspected within another branch.
        if (!isset($this->remainingNodes[$node->id])) {
            $branches[] = $branch;

            return;
        }

        if ($node instanceof DefeatNode || $node instanceof VictoryNode) {
            // This branch ends at this node.
            $branches[] = $branch;

            unset($this->remainingNodes[$node->id]);

            return;
        } elseif ($node instanceof DiceNode) {
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
            throw new LogicException("Node `$node->id` has an unexpected type");
        }

        foreach ($targetNodes as $targetNode) {
            $this->scanNode(node: $targetNode, branch: $branch, branches: $branches);

            // This node has already been fully inspected.
            unset($this->remainingNodes[$node->id]);
        }
    }
}
