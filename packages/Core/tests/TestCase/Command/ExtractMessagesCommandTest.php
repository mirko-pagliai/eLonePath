<?php
declare(strict_types=1);

namespace Elone\Core\Test\Command;

use Elone\Core\Command\ExtractMessagesCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * ExtractMessagesCommandTest.
 */
#[CoversClass(ExtractMessagesCommand::class)]
class ExtractMessagesCommandTest extends TestCase
{
    /**
     * The `.pot` files this test produces are generated output, not fixtures — they get a fresh temporary
     * directory each run and are cleaned up in `tearDown()`. The PHP source scanned for `__()`/`__d()` calls is
     * the opposite: static, checked-in fixtures under `tests/test_app/templates` — `--root` points at
     * `tests/test_app` itself; `PhpFileFinder` looks at its `templates/` (and `src/`) subdirectories on its own.
     * The rest of `templates/` — real Dispatcher/View fixtures other tests already use, with no `__()`/`__d()`
     * calls at all — is scanned right alongside the i18n ones and contributes nothing, the same as a real app's
     * templates would be.
     */
    private string $outputDir;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->outputDir = sys_get_temp_dir() . '/extract-messages-command-test-' . uniqid();

        mkdir($this->outputDir, recursive: true);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        if (is_dir($this->outputDir)) {
            $this->removeDirectory($this->outputDir);
        } elseif (file_exists($this->outputDir)) {
            unlink($this->outputDir);
        }
    }

    /**
     * Removes `$dir` and everything in it.
     *
     * @param string $dir
     * @return void
     */
    private function removeDirectory(string $dir): void
    {
        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = "$dir/$item";
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }

    /**
     * `i18n-one.php`/`i18n-two.php` between them cover: a message shared across two files (deduplicated), three
     * different domains (`default`, `validation`, `errors`), and a non-literal argument that must be skipped
     * rather than crash the whole scan. The rest of `templates/` — real Dispatcher/View fixtures with no
     * `__()`/`__d()` calls at all — is scanned right alongside them and contributes nothing, same as it would
     * in a real app.
     *
     * @link \Elone\Core\Command\ExtractMessagesCommand::__invoke()
     */
    #[Test]
    public function testInvokeWritesAPotFilePerDomainFound(): void
    {
        $tester = $this->execute();

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());

        $defaultPot = file_get_contents("$this->outputDir/default.pot");
        $this->assertIsString($defaultPot);
        $this->assertStringContainsString('msgid "Welcome back"', $defaultPot);
        $this->assertSame(1, substr_count($defaultPot, 'msgid "Welcome back"'));

        $validationPot = file_get_contents("$this->outputDir/validation.pot");
        $this->assertIsString($validationPot);
        $this->assertStringContainsString('msgid "The {0} attribute is required."', $validationPot);
    }

    /**
     * @link \Elone\Core\Command\ExtractMessagesCommand::__invoke()
     */
    #[Test]
    public function testInvokeSkipsNonLiteralArgumentsInsteadOfFailing(): void
    {
        $tester = $this->execute();

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());

        $defaultPot = file_get_contents("$this->outputDir/default.pot");
        $this->assertIsString($defaultPot);
        $this->assertStringNotContainsString('not a literal', $defaultPot);
    }

    /**
     * ICU plural syntax (`{count, plural, one {...} other {...}}`) lives entirely inside the message string
     * itself — this is what proves extraction treats it as ordinary text, needing no plural-aware handling of
     * its own; the whole thing survives the round trip through the scanner and the `.pot` writer untouched.
     *
     * @link \Elone\Core\Command\ExtractMessagesCommand::__invoke()
     */
    #[Test]
    public function testInvokeExtractsIcuPluralSyntaxVerbatim(): void
    {
        $tester = $this->execute();

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());

        $defaultPot = file_get_contents("$this->outputDir/default.pot");
        $this->assertIsString($defaultPot);
        $this->assertStringContainsString(
            'msgid "{count, plural, one {You have one message} other {You have # messages}}"',
            $defaultPot,
        );
    }

    /**
     * `i18n-one.php` calls `__d('errors', ...)` — a third domain, distinct from `default`/`validation`, which
     * the other tests here already show map to correctly-named files. This is what proves the naming isn't a
     * coincidence limited to those two: whatever domain a `__d()` call names, that's the `.pot` file it lands in.
     *
     * @link \Elone\Core\Command\ExtractMessagesCommand::__invoke()
     */
    #[Test]
    public function testInvokePotFileNameMatchesItsDomain(): void
    {
        $tester = $this->execute();

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertFileExists("$this->outputDir/errors.pot");

        $errorsPot = file_get_contents("$this->outputDir/errors.pot");
        $this->assertIsString($errorsPot);
        $this->assertStringContainsString('msgid "Something went wrong."', $errorsPot);
    }

    /**
     * If `$localesDir` doesn't exist yet, `__invoke()` creates it — this fixture starts from a path one level
     * below `$this->outputDir` that has never been created, proving that, not just that an already-existing
     * directory gets written into.
     *
     * @link \Elone\Core\Command\ExtractMessagesCommand::__invoke()
     */
    #[Test]
    public function testInvokeCreatesLocalesDirWhenMissing(): void
    {
        $freshDir = "$this->outputDir/not-created-yet";
        $this->assertDirectoryDoesNotExist($freshDir);

        $application = new Application();
        $application->addCommand(new ExtractMessagesCommand());
        $tester = new CommandTester($application->find('i18n:extract'));
        $tester->execute([
            '--root' => TEST_APP,
            '--locales-dir' => $freshDir,
        ]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertFileExists("$freshDir/default.pot");
    }

    /**
     * `$localesDir` pointing at a path that's already a *file* can't be turned into a directory — `mkdir()`
     * fails, and stays failed, so `__invoke()` must report it rather than crash on the `file_put_contents()`
     * calls further down.
     *
     * @link \Elone\Core\Command\ExtractMessagesCommand::__invoke()
     */
    #[Test]
    public function testInvokeFailsWhenLocalesDirCannotBeCreated(): void
    {
        $blockedPath = "$this->outputDir/blocked";
        file_put_contents($blockedPath, '');

        $application = new Application();
        $application->addCommand(new ExtractMessagesCommand());
        $tester = new CommandTester($application->find('i18n:extract'));
        $tester->execute([
            '--root' => TEST_APP,
            '--locales-dir' => $blockedPath,
        ]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Unable to create directory', $tester->getDisplay());
    }

    /**
     * `checkHasPhpScanner()` overridden to force the "package missing" state — the same technique
     * `HtmlHelperTest::testMarkdownPackageIsMissing()` uses for `checkHasMarkdown()`. `gettext/php-scanner` is
     * an installed dev dependency during a normal test run, so this is the only way to exercise this branch.
     * `setName()` is needed because `#[AsCommand]` isn't inherited by an anonymous subclass.
     *
     * @link \Elone\Core\Command\ExtractMessagesCommand::__invoke()
     */
    #[Test]
    public function testInvokeFailsWhenPhpScannerIsMissing(): void
    {
        $command = new class () extends ExtractMessagesCommand {
            protected function checkHasPhpScanner(): bool
            {
                return false;
            }
        };
        $command->setName('i18n:extract');

        $application = new Application();
        $application->addCommand($command);
        $tester = new CommandTester($application->find('i18n:extract'));
        $tester->execute([]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString(
            'gettext/gettext and gettext/php-scanner are required',
            $tester->getDisplay(),
        );
    }

    /**
     * Runs `i18n:extract` against `tests/test_app`, writing into `$this->outputDir`.
     *
     * @return \Symfony\Component\Console\Tester\CommandTester
     */
    private function execute(): CommandTester
    {
        $application = new Application();
        $application->addCommand(new ExtractMessagesCommand());

        $tester = new CommandTester($application->find('i18n:extract'));
        $tester->execute([
            '--root' => TEST_APP,
            '--locales-dir' => $this->outputDir,
        ]);

        return $tester;
    }
}
