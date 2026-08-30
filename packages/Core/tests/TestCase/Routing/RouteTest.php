<?php
declare(strict_types=1);

namespace Elone\Core\Test\Routing;

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
     * Tests for the `__construct()` method with an invalid controller name.
     *
     * @link \Elone\Core\Routing\Route::__construct()
     */
    #[Test]
    #[TestWith(['2'])]
    #[TestWith(['2222'])]
    #[TestWith(['Pages2Controller'])]
    #[TestWith(['pagesController'])]
    public function testConstructInvalidControllerName(string $controllerName): void
    {
        $this->expectException(ControllerNotFoundException::class);
        $this->expectExceptionMessageIs("Invalid controller name: `$controllerName`.");
        new Route(controller: $controllerName, action: 'action');
    }

    /**
     * Tests for the `__construct()` method with a controller that does not exist.
     *
     * @link \Elone\Core\Routing\Route::__construct()
     */
    #[Test]
    public function testConstructControllerNotFound(): void
    {
        $this->expectException(ControllerNotFoundException::class);
        $this->expectExceptionMessageIs("Controller not found: `TestApp\Controller\NoExistingController`.");
        new Route(controller: 'NoExisting', action: 'action');
    }

    /**
     * Test for the `__construct()` method with a class that does not extend `Controller`.
     *
     * @link \Elone\Core\Routing\Route::__construct()
     */
    #[Test]
    public function testConstructClassDoesNotExtendController(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessageIs('`TestApp\Controller\BadController` must extend `' . Controller::class . '`.');
        new Route(controller: 'Bad', action: 'action');
    }

    /**
     * Test for the `__construct()` method with no existing action method.
     *
     * @link \Elone\Core\Routing\Route::__construct()
     */
    #[Test]
    public function testConstructActionNotFound(): void
    {
        $this->expectException(ActionNotFoundException::class);
        $this->expectExceptionMessageIs('Action not found: `TestApp\Controller\PagesController::noExistingMethod()`.');
        new Route(controller: 'Pages', action: 'noExistingMethod');
    }

    /**
     * Test for the `__construct()` method with no existing action method.
     *
     * @link \Elone\Core\Routing\Route::__construct()
     */
    #[Test]
    public function testConstructActionIsNotPublic(): void
    {
        $this->expectException(ActionNotFoundException::class);
        $this->expectExceptionMessageIs('Action is not public: `TestApp\Controller\PagesController::invalidAction()`.');
        new Route(controller: 'Pages', action: 'invalidAction');
    }

    /**
     * @link \Elone\Core\Routing\Route::controllerClass()
     */
    #[Test]
    public function testControllerClass(): void
    {
        $route = new Route(controller: 'Pages', action: 'home');
        $result = $route->controllerClass();
        $this->assertSame('TestApp\Controller\PagesController', $result);
    }

    /**
     * @link \Elone\Core\Routing\Route::path()
     */
    #[Test]
    public function testPath(): void
    {
        $route = new Route(controller: 'Pages', action: 'home');
        $result = $route->path();
        $this->assertSame('/pages/home', $result);
    }
}
