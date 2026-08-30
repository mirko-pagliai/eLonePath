<?php
declare(strict_types=1);

namespace Elone\Core\Test\Core\Routing;

use Elone\Core\Controller;
use Elone\Core\Exception\ActionNotFoundException;
use Elone\Core\Exception\ControllerNotFoundException;
use Elone\Core\Exception\RouteNotFoundException;
use Elone\Core\Routing\Route;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * RouteTest.
 */
#[CoversClass(Route::class)]
class RouteTest extends TestCase
{
    /**
     * @link \Elone\Core\Routing\Route::__construct()
     */
    #[Test]
    #[TestWith([RouteNotFoundException::class, 'Invalid controller name: `2`.', '2'])]
    #[TestWith([ControllerNotFoundException::class, 'Controller not found: `TestApp\Controller\BadControllerController`.', 'badController'])]
    #[TestWith([ControllerNotFoundException::class, 'Controller not found: `TestApp\Controller\NoExistingController`.', 'noExisting'])]
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
        $this->expectExceptionMessageIs('`TestApp\Controller\BadController` must extend `' . Controller::class . '`.');
        new Route('bad', 'action');
    }

    /**
     * Test for the `__construct()` method with no existing action method
     */
    #[Test]
    public function testConstructWithNoExistingActionMethod(): void
    {
        $this->expectException(ActionNotFoundException::class);
        $this->expectExceptionMessageIs('Action not found: `TestApp\Controller\PagesController::noExistingMethod()`.');
        new Route('pages', 'noExistingMethod');
    }
}
