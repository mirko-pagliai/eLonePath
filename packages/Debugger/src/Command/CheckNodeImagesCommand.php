<?php
declare(strict_types=1);

namespace Elone\Debugger\Command;

use App\Story\Game;
use Elone\Debugger\NodeImagesWalker;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Run with:
 * ```
 * $ bin/console debugger:check-node-images full/path/to/story.json
 * ```
 */
#[AsCommand(name: 'debugger:check-node-images')]
class CheckNodeImagesCommand extends Command
{
    public function __invoke(
        #[Argument('The `story.json` file you want to test')] string $filename,
        SymfonyStyle $io,
    ): int {
        $game = Game::createFromFile(path: $filename);

        if ($io->isVerbose()) {
            $this->printGameHeaders(output: $io, game: $game);
        }

        $walker = new NodeImagesWalker($game);
        $errors = $walker();

        if ($errors) {
            foreach ($errors as $error) {
                $io->error("<error>$error</error>");
            }

            return Command::FAILURE;
        }

        if ($io->isVerbose()) {
            $io->success('All node images are valid');
        }

        return Command::SUCCESS;
    }
}
