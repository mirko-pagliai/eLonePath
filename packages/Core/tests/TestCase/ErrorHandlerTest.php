<?php
declare(strict_types=1);

namespace Elone\Core\Test;

use Closure;
use Elone\Core\ErrorHandler;
use Elone\Core\Exception\ActionNotFoundException;
use Elone\Core\View\View;
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
        $errorHandler = new ErrorHandler(logger: $this->logger);

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
        $errorHandler = new ErrorHandler(debug: false, logger: $this->logger);

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
        $errorHandler = new ErrorHandler(debug: true, logger: $this->logger);

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
    public function testHandleFallsBackWhenViewRenderFails(): void
    {
        $view = new class extends View {
            public function render(string $template, ?string $layout = 'default'): string
            {
                throw new RuntimeException('Boom.');
            }
        };

        $errorHandler = new ErrorHandler(view: $view, logger: $this->logger);

        $response = $errorHandler->handle(new RuntimeException('Anything.'));

        $this->assertSame(500, $response->status());
        $this->assertSame('Internal Server Error', $response->content());
    }
}
