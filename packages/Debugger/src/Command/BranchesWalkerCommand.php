<?php
declare(strict_types=1);

namespace Elone\Debugger\Command;

use App\Story\Game;
use App\Story\Nodes\DefeatNode;
use App\Story\Nodes\DiceNode;
use App\Story\Nodes\Node;
use App\Story\Nodes\VictoryNode;
use Elone\Debugger\BranchesWalker;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Run with:
 * ```
 * $ bin/console debugger:branches-walker full/path/to/story.json
 * ```
 */
#[AsCommand(name: 'debugger:branches-walker')]
class BranchesWalkerCommand extends Command
{
    public function __invoke(
        #[Argument('The `story.json` file you want to test')] string $filename,
        SymfonyStyle $io,
    ): int {
        $game = Game::createFromFile(path: $filename);

        if ($io->isVerbose()) {
            $this->printGameHeaders(output: $io, game: $game);
        }

        $nodesWalker = new BranchesWalker(game: $game);
        $branches = $nodesWalker();

        if ($io->isVerbose()) {
            $io->info('Check all the branches are walkable...');

            // Extracts node IDs for comparison.
            $extractIdForCmp = fn(Node $node): string => $node->id < 10 ? "0$node->id" : "$node->id";
            usort(
                array: $branches,
                callback: fn(array $branch, array $anotherBranch): int => strcmp(
                    implode('', array_map(callback: $extractIdForCmp, array: $branch)),
                    implode('', array_map(callback: $extractIdForCmp, array: $anotherBranch)),
                ),
            );

            foreach ($branches as $branch) {
                foreach ($branch as $k => $node) {
                    $io->write("$node->id ");

                    if ($node instanceof VictoryNode) {
                        $io->write('> <fg=green>victory</>');
                    } elseif ($node instanceof DefeatNode) {
                        $io->write('> <fg=red>defeat</>');
                    } elseif ($node instanceof DiceNode) {
                        $io->write('> <fg=blue>dice with ' . $node->requiredRolls . ' rolls</> ');
                    }

                    if (array_key_last($branch) === $k) {
                        continue;
                    }

                    $io->write('> ');
                }

                $io->newLine();
            }

            $io->newLine();
        }

        $defeatBranches = array_filter(
            array: $branches,
            callback: fn($branch): bool => array_last($branch) instanceof DefeatNode,
        );
        $winningBranches = array_filter(
            array: $branches,
            callback: fn($branch): bool => array_last($branch) instanceof VictoryNode,
        );

        $io->writeln('Branches: ' . count($branches));
        $io->writeln('Winning branches: ' . count($winningBranches));
        $io->writeln('Defeat branches: ' . count($defeatBranches));
        $io->writeln('Remaining nodes: ' . count($nodesWalker->getRemainingNodes()));

        return Command::SUCCESS;
    }
}
