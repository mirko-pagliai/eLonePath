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
 * $ bin/console debugger:check-branches full/path/to/story.json
 * ```
 */
#[AsCommand(
    name: 'debugger:check-branches',
    // this short description is shown when running "php bin/console list"
    description: 'Checks all narrative branches, crossing them all',
)]
class CheckBranchesCommand extends Command
{
    public function __invoke(
        #[Argument('The `story.json` file you want to test')] string $filename,
        SymfonyStyle $io,
    ): int {
        $game = Game::createFromFile(path: $filename);

        if ($io->isVerbose()) {
            $this->printGameHeaders(output: $io, game: $game);
        }

        $walker = new BranchesWalker(game: $game);

        if ($io->isVerbose()) {
            $io->newLine();

            $branches = $walker->getAllBranches();

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

                    // Style for some node types
                    if ($node instanceof VictoryNode) {
                        $io->write('> <fg=green>victory</>');
                    } elseif ($node instanceof DefeatNode) {
                        $io->write('> <fg=red>defeat</>');
                    } elseif ($node instanceof DiceNode) {
                        $io->write('> <fg=blue>dice with ' . $node->requiredRolls . ' rolls</> ');
                    }

                    // Style for image nodes
                    $image = Node::extractLeadingImage($node->content);
                    if ($image['path'] !== null) {
                        $io->write("> image `<fg=yellow>{$image['path']}</>` ");
                    }

                    if (array_key_last($branch) === $k) {
                        continue;
                    }

                    $io->write('> ');
                }

                $io->newLine();
            }

            $io->newLine();

            $io->writeln('Branches: ' . count($walker->getAllBranches()));
            $io->writeln('Winning branches: ' . count($walker->getWinningBranches()));
            $io->writeln('Defeat branches: ' . count($walker->getDefeatBranches()));
            $io->writeln('Remaining nodes: ' . count($walker->getRemainingNodes()));
        }

        $errors = $walker();

        if ($errors) {
            foreach ($errors as $error) {
                $io->error($error);
            }

            return Command::FAILURE;
        }

        if ($io->isVerbose()) {
            $io->success('All branches are valid');
        }

        return Command::SUCCESS;
    }
}
