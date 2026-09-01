<?php
declare(strict_types=1);

namespace App\Debugger;

use App\Story\Game;
use App\Story\Nodes\Choice;
use App\Story\Nodes\DefeatNode;
use App\Story\Nodes\DiceNode;
use App\Story\Nodes\Node;
use App\Story\Nodes\PassageNode;
use App\Story\Nodes\VictoryNode;
use Exception;

class NodesWalker
{
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
        $branches = [];

        $this->walk(
            node: $this->game->getNode(1),
            branch: [],
            branches: $branches,
        );

        return $branches;
    }

    protected function walk(
        Node $node,
        array $branch,
        array &$branches,
    ): void {
        /*
         * Questo nodo è già stato completamente analizzato
         * da un altro ramo.
         */
        if (!isset($this->remainingNodes[$node->id])) {
            return;
        }

        $branch[] = $node;

        if ($node instanceof DefeatNode || $node instanceof VictoryNode) {
            $branches[] = $branch;

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
            throw new Exception('Unexpected node type');
        }

        foreach ($targetNodes as $targetNode) {
            $this->walk(
                node: $targetNode,
                branch: $branch,
                branches: $branches,
            );

            /*
             * SOLO ADESSO il nodo è completamente esaurito.
             */
            unset($this->remainingNodes[$node->id]);
        }
    }

    /**
     * @return array<\App\Story\Nodes\Node>
     */
    public function getRemainingNodes(): array
    {
        return $this->remainingNodes;
    }
}
