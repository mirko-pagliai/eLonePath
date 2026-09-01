<?php
declare(strict_types=1);

namespace App\Command;

use App\Debugger\NodesWalker;
use App\Story\Game;
use App\Story\Nodes\PassageNode;
use App\Story\Nodes\VictoryNode;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Run with:
 * ```
 * $ php bin/console test-story full/path/to/story.json
 * ```
 */
#[AsCommand(name: 'test-story')]
class StoryTesterCommand extends Command
{
    public function __invoke(
        #[Argument('The `story.json` file you want to test')] string $filename,
        SymfonyStyle $io,
    ): int {
        // Checks if the file is a `story.json` file
        if (!str_ends_with($filename, 'story.json')) {
            $io->error('The file must be a `story.json` file');

            return Command::FAILURE;
        }

        $game = Game::createFromFile(path: $filename);
        $nodesWalker = new NodesWalker(game: $game);

        $tree = $nodesWalker();
        foreach ($tree as $branch) {
            foreach ($branch as $node) {
                $text = "$node->id ";

                if (!$node instanceof PassageNode) {
                    $text .= '(' . $node->type()->value . ')';
                }

                if (array_last($branch) !== $node) {
                    $text .= ' > ';
                }

                $io->write($text);
            }

            $io->writeln('');
        }

        $winningBranches = array_filter(
            array: $tree,
            callback: function ($branch) {
                $lastNode = array_last($branch);

                return $lastNode instanceof VictoryNode;
            },
        );

        $io->newLine();
        $io->writeln('Branches: ' . count($tree));
        $io->writeln('Winning branches: ' . count($winningBranches));
        $io->writeln('Remaining nodes: ' . count($nodesWalker->getRemainingNodes()));
        exit;

        $io->writeln('Game headers');
        $this->printGameHeaders($io, $game);

        $io->write('Branches: ' . count($tree));
        $io->write('Remaining nodes: ' . count($tree->getRemainingNodes()));
        exit;

        $io->writeln('Game headers');
        $this->printGameHeaders($io, $game);

        // Checks if the images are valid
        $this->checkImagesSizes(io: $io, game: $game);

        return Command::SUCCESS;
    }

    protected function checkImagesSizes(SymfonyStyle $io, Game $game): void
    {
        $io->comment('Image check...');

        $images = array_filter(array_column(array: $game->nodes, column_key: 'image'));

        $io->info('Found ' . count($images) . ' images');

        $hasErrors = false;

        foreach ($images as $nodeId => $image) {
            if (!isset($image['path']) || !trim($image['path'])) {
                $io->error("Node `$nodeId` image does not have the `path` attribute or is empty");
                $hasErrors = true;

                continue;
            }
            if (!isset($image['title']) || !trim($image['title'])) {
                $io->error("Node `$nodeId` image does not have the `title` attribute or is empty");
                $hasErrors = true;
            }

            $fullPath = STORIES . "/$game->gameId/img/{$image['path']}";

            $info = getimagesize($fullPath);

            if ($info === false || !in_array($info['mime'], ['image/jpeg', 'image/png', 'image/gif'], true)) {
                $io->error("Node `$nodeId` image is not a valid image (jpeg, png or gif)");
                $hasErrors = true;

                continue;
            }

            if ($info[0] > 960) {
                $io->caution("Image `$fullPath` is too wide (max 800px, actual $info[0]px)");
                $hasErrors = true;
            }
            if ($info[1] > 800) {
                $io->caution("Image `$fullPath` is too high (max 800px, actual $info[1]px)");
            }
        }

        if (!$hasErrors) {
            $io->success('All images are valid!');
        }
    }

    /**
     * Renders the header information of the game as a table in the given output.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output The output interface.
     * @param \App\Story\Game $game The game object containing the data to be displayed in the header.
     * @return void
     */
    protected function printGameHeaders(OutputInterface $output, Game $game): void
    {
        $gameAsArray = array_filter((array)$game);
        unset($gameAsArray['nodes']);

        $table = new Table($output);
        array_walk($gameAsArray, fn($value, $key) => $table->addRow([$key, $value]));
        $table->render();
    }
}
