<?php
declare(strict_types=1);

namespace TestCase\Core\Routing;

use App\Core\Controller;
use App\Core\Exception\ActionNotFoundException;
use App\Core\Exception\ControllerNotFoundException;
use App\Core\Exception\RouteNotFoundException;
use App\Core\Routing\Route;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * RouteTest.
 */
#[CoversClass(Route::class)]
class RouteTest extends TestCase
{
    #[Test]
    #[TestWith([RouteNotFoundException::class, 'Invalid controller name: `2`.', '2'])]
    #[TestWith([ControllerNotFoundException::class, 'Controller not found: `App\Controller\BadControllerController`.', 'badController'])]
    #[TestWith([ControllerNotFoundException::class, 'Controller not found: `App\Controller\BadController`.', 'bad'])]
    public function testConstructWithInvalidControllers(string $exceptionClass, string $exceptionMessage, string $controllerName): void
    {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessageIs($exceptionMessage);
        new Route($controllerName, 'action');
    }

    /**
     * Test for the `__construct` method with a class that does not extend `Controller`.
     */
    #[Test]
    public function testConstructClassDoesNotExtendController(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessageIs('stdClass must extend `' . Controller::class . '`.');
        new readonly class ('bad', 'action') extends Route {
            public function controllerClass(): string
            {
                return stdclass::class;
            }
        };
    }

    /**
     * Test for the `__construct()` method with no existing action method
     */
    #[Test]
    public function testConstructWithNoExistingActionMethod(): void
    {
        $this->expectException(ActionNotFoundException::class);
        $this->expectExceptionMessageIs('Action not found: `App\Controller\PagesController::noExistingMethod()`.');
        new Route('pages', 'noExistingMethod');
    }
}
