<?php
declare(strict_types=1);

namespace Elone\Core\Test;

use Elone\Core\Application;
use Elone\Core\Configuration;
use Elone\Core\Dispatcher;
use Elone\Core\ErrorHandler;
use Elone\Core\Routing\Router;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * ApplicationTest.
 */
#[CoversClass(Application::class)]
class ApplicationTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $originalServer;

    private Application $application;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->originalServer = $_SERVER;

        $configuration = new Configuration();
        $router = new Router();
        $dispatcher = new Dispatcher();
        $errorHandler = new ErrorHandler(
            configuration: $configuration,
            logger: function (string $message): void {
            },
        );

        $this->application = new Application($router, $dispatcher, $errorHandler);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
    }

    /**
     * @link \Elone\Core\Application::run()
     */
    #[Test]
    public function testRunDispatchesToController(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/response/ok';

        ob_start();
        $this->application->run();
        $output = ob_get_clean();

        $this->assertSame('Hello from ResponseController.', $output);
        $this->assertSame(200, http_response_code());
    }

    /**
     * @link \Elone\Core\Application::run()
     */
    #[Test]
    public function testRunFallsBackToErrorHandlerOnInvalidRoute(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/no-such-controller';

        ob_start();
        $this->application->run();
        $output = ob_get_clean();

        $this->assertSame(404, http_response_code());
        $this->assertStringContainsString('404', $output);
    }
}
