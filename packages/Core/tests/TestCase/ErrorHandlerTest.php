<?php
declare(strict_types=1);

namespace Elone\Core\Test;

use Closure;
use Elone\Core\Configuration;
use Elone\Core\ErrorHandler;
use Elone\Core\Exception\ActionNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * ErrorHandlerTest.
 */
#[CoversClass(ErrorHandler::class)]
class ErrorHandlerTest extends TestCase
{
    /**
     * @var list<string>
     */
    private array $loggedMessages;

    private Closure $logger;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->loggedMessages = [];
        $this->logger = function (string $message): void {
            $this->loggedMessages[] = $message;
        };
    }

    /**
     * @link \Elone\Core\ErrorHandler::handle()
     */
    #[Test]
    public function testHandleHttpException(): void
    {
        $configuration = new Configuration(TEST_APP, 'TestApp');
        $errorHandler = new ErrorHandler($configuration, $this->logger);

        $response = $errorHandler->handle(new ActionNotFoundException('Action not found: `Foo::bar()`.'));

        $this->assertSame(404, $response->status());
        $this->assertStringContainsString('404', $response->content());
        $this->assertStringContainsString('Action not found: `Foo::bar()`.', $response->content());
    }

    /**
     * @link \Elone\Core\ErrorHandler::handle()
     */
    #[Test]
    public function testHandleGenericExceptionWithoutDebug(): void
    {
        $configuration = new Configuration(TEST_APP, 'TestApp', debug: false);
        $errorHandler = new ErrorHandler($configuration, $this->logger);

        $response = $errorHandler->handle(new RuntimeException('Some internal detail.'));

        $this->assertSame(500, $response->status());
        $this->assertStringContainsString('Internal Server Error', $response->content());
        $this->assertStringNotContainsString('Some internal detail.', $response->content());
        $this->assertCount(1, $this->loggedMessages);
        $this->assertStringContainsString('Some internal detail.', $this->loggedMessages[0]);
    }

    /**
     * @link \Elone\Core\ErrorHandler::handle()
     */
    #[Test]
    public function testHandleGenericExceptionWithDebug(): void
    {
        $configuration = new Configuration(TEST_APP, 'TestApp', debug: true);
        $errorHandler = new ErrorHandler($configuration, $this->logger);

        $response = $errorHandler->handle(new RuntimeException('Some internal detail.'));

        $this->assertSame(500, $response->status());
        $this->assertStringContainsString('Some internal detail.', $response->content());
        $this->assertStringContainsString('RuntimeException', $response->content());
        $this->assertSame([], $this->loggedMessages);
    }

    /**
     * @link \Elone\Core\ErrorHandler::handle()
     */
    #[Test]
    public function testHandleFallsBackWhenTemplatesPathIsMissing(): void
    {
        $configuration = new Configuration('/path/does/not/exist', 'TestApp');
        $errorHandler = new ErrorHandler($configuration, $this->logger);

        $response = $errorHandler->handle(new RuntimeException('Anything.'));

        $this->assertSame(500, $response->status());
        $this->assertSame('Internal Server Error', $response->content());
    }
}
