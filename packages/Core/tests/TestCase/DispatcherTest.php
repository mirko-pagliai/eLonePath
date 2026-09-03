<?php
declare(strict_types=1);

namespace Elone\Core\Test;

use Elone\Core\Dispatcher;
use Elone\Core\Exception\HttpException;
use Elone\Core\Exception\UnsupportedParameterTypeException;
use Elone\Core\Routing\Route;
use Elone\Core\Server\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use TestApp\Controller\ConversionController;

/**
 * DispatcherTest.
 */
#[CoversClass(Dispatcher::class)]
class DispatcherTest extends TestCase
{
    /**
     * `PagesController::home()` returns `void` — this is what proves `dispatch()` genuinely falls through to
     * `templateName()` and `$controller->render()` for a real template file (`pages/home.php`, via the default
     * layout), rather than the whole path only ever being exercised through `resolveArguments()`/`templateName()`
     * in isolation, or through an action that returns a `Response` directly.
     *
     * @link \Elone\Core\Dispatcher::dispatch()
     */
    #[Test]
    public function testDispatchRendersTheControllerActionsTemplate(): void
    {
        $dispatcher = new Dispatcher();
        $route = new Route(controller: 'Pages', action: 'home');
        $request = new Request('GET', '/pages/home');

        $response = $dispatcher->dispatch($route, $request);

        $this->assertSame(200, $response->status());
        $this->assertStringContainsString('Home page.', $response->content());
    }

    /**
     * @link \Elone\Core\Dispatcher::templateName()
     */
    #[Test]
    #[TestWith(['pages/index', 'Pages', 'index'])]
    #[TestWith(['pages/view', 'Pages', 'view'])]
    #[TestWith(['users-settings/view', 'UsersSettings', 'view'])]
    public function testTemplateName(string $expectedTemplateName, string $controller, string $action): void
    {
        $dispatcher = new readonly class extends Dispatcher {
            public function templateName(Route $route): string
            {
                return parent::templateName($route);
            }
        };

        $route = new readonly class (controller: $controller, action: $action) extends Route {
            public function __construct(public string $controller, public string $action, public array $params = [])
            {
            }
        };

        $result = $dispatcher->templateName($route);
        $this->assertSame($expectedTemplateName, $result);
    }

    /**
     * @param list<string> $params
     * @param list<mixed> $expected
     *
     * @link \Elone\Core\Dispatcher::resolveArguments()
     */
    #[Test]
    #[TestWith(['withInt', ['42'], [42]])]
    #[TestWith(['withFloat', ['3.5'], [3.5]])]
    #[TestWith(['withBool', ['yes'], [true]])]
    #[TestWith(['withBool', ['no'], [false]])]
    #[TestWith(['withString', ['hello'], ['hello']])]
    public function testResolveArguments(string $action, array $params, array $expected): void
    {
        $dispatcher = new readonly class extends Dispatcher {
            public static function resolveArguments(ReflectionMethod $method, array $params): array
            {
                return parent::resolveArguments($method, $params);
            }
        };

        $method = new ReflectionMethod(ConversionController::class, $action);

        $result = $dispatcher::resolveArguments($method, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * @link \Elone\Core\Dispatcher::resolveArguments()
     */
    #[Test]
    #[TestWith(['withInt', 'not-a-number', "Invalid integer parameter 'not-a-number' for `\$value`."])]
    #[TestWith(['withFloat', 'not-a-number', "Invalid float parameter 'not-a-number' for `\$value`."])]
    #[TestWith(['withBool', 'maybe', "Invalid boolean parameter 'maybe' for `\$value`."])]
    public function testResolveArgumentsWithInvalidValue(string $action, string $value, string $expectedMessage): void
    {
        $dispatcher = new readonly class extends Dispatcher {
            public static function resolveArguments(ReflectionMethod $method, array $params): array
            {
                return parent::resolveArguments($method, $params);
            }
        };

        $method = new ReflectionMethod(ConversionController::class, $action);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessageIs($expectedMessage);
        $dispatcher::resolveArguments($method, [$value]);
    }

    /**
     * @link \Elone\Core\Dispatcher::resolveArguments()
     */
    #[Test]
    public function testResolveArgumentsWithUnsupportedType(): void
    {
        $dispatcher = new readonly class extends Dispatcher {
            public static function resolveArguments(ReflectionMethod $method, array $params): array
            {
                return parent::resolveArguments($method, $params);
            }
        };

        $method = new ReflectionMethod(ConversionController::class, 'withArray');

        $this->expectException(UnsupportedParameterTypeException::class);
        $this->expectExceptionMessageIs("Unsupported parameter type 'array' for `\$value`.");
        $dispatcher::resolveArguments($method, ['irrelevant']);
    }

    /**
     * @link \Elone\Core\Dispatcher::resolveArguments()
     */
    #[Test]
    public function testResolveArgumentsWithWrongParameterCount(): void
    {
        $dispatcher = new readonly class extends Dispatcher {
            public static function resolveArguments(ReflectionMethod $method, array $params): array
            {
                return parent::resolveArguments($method, $params);
            }
        };

        $controller = ConversionController::class;
        $method = new ReflectionMethod($controller, 'withInt');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessageIs(
            "Invalid number of parameters for `$controller::withInt()`. Expected 1, received 0.",
        );
        $dispatcher::resolveArguments($method, []);
    }
}
