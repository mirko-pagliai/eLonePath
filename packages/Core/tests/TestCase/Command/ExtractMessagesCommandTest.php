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
     * the opposite: static, checked-in fixtures under `tests/test_app/templates` — the same directory other
     * tests already use for Dispatcher/View fixtures, mixed in among files with no `__()`/`__d()` calls at all,
     * the same as a real app's templates would be.
     */
    private string $outputDir;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->outputDir = sys_get_temp_dir() . '/extract-messages-command-test-' . uniqid();

        mkdir($this->outputDir, recursive: true);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->removeDirectory($this->outputDir);
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
        $this->assertStringContainsString('msgid "Welcome back"', $defaultPot);
        $this->assertSame(1, substr_count($defaultPot, 'msgid "Welcome back"'));

        $validationPot = file_get_contents("$this->outputDir/validation.pot");
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
        $this->assertStringNotContainsString(
            'not a literal',
            file_get_contents("$this->outputDir/default.pot"),
        );
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
        $this->assertStringContainsString(
            'msgid "{count, plural, one {You have one message} other {You have # messages}}"',
            file_get_contents("$this->outputDir/default.pot"),
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
        $this->assertStringContainsString(
            'msgid "Something went wrong."',
            file_get_contents("$this->outputDir/errors.pot"),
        );
    }

    /**
     * Runs `i18n:extract` against `tests/test_app/templates`, writing into `$this->outputDir`.
     *
     * @return \Symfony\Component\Console\Tester\CommandTester
     */
    private function execute(): CommandTester
    {
        $application = new Application();
        $application->addCommand(new ExtractMessagesCommand());

        $tester = new CommandTester($application->find('i18n:extract'));
        $tester->execute([
            '--root' => __DIR__ . '/../../test_app/templates',
            '--locales-dir' => $this->outputDir,
        ]);

        return $tester;
    }
}
