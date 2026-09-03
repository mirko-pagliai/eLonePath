<?php
declare(strict_types=1);

namespace Elone\Core\Test\Routing;

use Elone\Core\Routing\Router;
use Elone\Core\Server\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * RouterTest.
 */
#[CoversClass(Router::class)]
class RouterTest extends TestCase
{
    /**
     * @param list<string> $expectedParams
     *
     * @link \Elone\Core\Routing\Router::dispatch()
     */
    #[Test]
    #[TestWith(['/', 'Pages', 'home', []])]
    #[TestWith(['/pages/home', 'Pages', 'home', []])]
    #[TestWith(['/pages/view/123', 'Pages', 'view', ['123']])]
    #[TestWith(['/users-settings', 'UsersSettings', 'index', []])]
    public function testDispatch(
        string $path,
        string $expectedController,
        string $expectedAction,
        array $expectedParams,
    ): void {
        $router = new Router();
        $route = $router->dispatch(new Request('GET', $path));

        $this->assertSame($expectedController, $route->controller);
        $this->assertSame($expectedAction, $route->action);
        $this->assertSame($expectedParams, $route->params);
    }
}
