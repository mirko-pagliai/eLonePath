<?php
declare(strict_types=1);

namespace Elone\Core\Command;

use Elone\Core\Console\DomainDiscoverer;
use Elone\Core\Console\PhpFileFinder;
use Gettext\Generator\PoGenerator;
use Gettext\Scanner\PhpScanner;
use Gettext\Translations;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Run with:
 * ```
 * $ bin/console i18n:extract
 * ```
 */
#[AsCommand(
    name: 'i18n:extract',
    // this short description is shown when running "php bin/console list"
    description: 'Scans for __()/__d() calls and updates the .pot template for each domain found',
)]
final class ExtractMessagesCommand extends Command
{
    /**
     * Scans `$root` for `__()`/`__d()` calls and writes a `.pot` file per domain found into `$localesDir`.
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     * @param string|null $root Directory to scan for `__()`/`__d()` calls. Defaults to `ROOT`.
     * @param string|null $localesDir Directory to write `.pot` files into. Defaults to `LOCALES`.
     * @return int `Command::SUCCESS`.
     */
    public function __invoke(
        SymfonyStyle $io,
        #[Option('Directory to scan for __()/__d() calls; defaults to `ROOT`')] ?string $root = null,
        #[Option('Directory to write .pot files into; defaults to `LOCALES`')] ?string $localesDir = null,
    ): int {
        $root ??= ROOT;
        $localesDir ??= LOCALES;

        $files = iterator_to_array(new PhpFileFinder()->find($root), false);

        $domains = new DomainDiscoverer()->discover($files);

        $translationsByDomain = [];
        foreach ($domains as $domain) {
            $translationsByDomain[$domain] = Translations::create($domain);
        }

        $scanner = new PhpScanner(...array_values($translationsByDomain));
        $scanner->setFunctions($scanner->getFunctions() + ['__d' => 'dgettext']);
        $scanner->setDefaultDomain('default');
        $scanner->ignoreInvalidFunctions(true);

        foreach ($files as $file) {
            $scanner->scanFile($file);
        }

        $generator = new PoGenerator();

        foreach ($scanner->getTranslations() as $domain => $scanned) {
            if (count($scanned) === 0) {
                continue;
            }

            $path = "$localesDir/$domain.pot";

            file_put_contents($path, $generator->generateString($scanned));

            if ($io->isVerbose()) {
                $io->writeln("$domain.pot: " . count($scanned) . ' message(s) found in code');
            }
        }

        $io->success('Extraction complete.');

        return Command::SUCCESS;
    }
}
