<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

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
        OutputInterface $output,
    ): int {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            '============',
            'Story tester',
            '============',
            '',
        ]);

        $output->writeln('Filename: ' . $filename);

        return Command::SUCCESS;
    }
}
