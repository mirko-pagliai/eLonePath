<?php
declare(strict_types=1);

namespace Elone\Debugger\Command;

use App\Story\Game;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base class for commands.
 */
abstract class Command extends SymfonyCommand
{
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
