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
     * the opposite: static, checked-in fixtures under `tests/test_app/i18n`, not files this test writes itself.
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
     * Two fixture files, one shared message, one domain-specific message, and one non-literal call that must be
     * skipped rather than crash the whole scan — this is what actually proves extraction works end to end, not
     * just that its individual pieces do in isolation.
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
     * Runs `i18n:extract` against the static fixtures in `tests/test_app/i18n`, writing into `$this->outputDir`.
     *
     * @return \Symfony\Component\Console\Tester\CommandTester
     */
    private function execute(): CommandTester
    {
        $application = new Application();
        $application->addCommand(new ExtractMessagesCommand());

        $tester = new CommandTester($application->find('i18n:extract'));
        $tester->execute([
            '--root' => __DIR__ . '/../../test_app/i18n',
            '--locales-dir' => $this->outputDir,
        ]);

        return $tester;
    }
}
