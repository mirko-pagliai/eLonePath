<?php
declare(strict_types=1);

namespace Elone\Core\Test;

use Elone\Core\Configuration;
use Elone\Core\Dispatcher;
use Elone\Core\Routing\Route;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * DispatcherTest.
 */
#[CoversClass(Dispatcher::class)]
class DispatcherTest extends TestCase
{
    /**
     * @link \Elone\Core\Dispatcher::templateName()
     */
    #[Test]
    #[TestWith(['pages/index', 'Pages', 'index'])]
    #[TestWith(['pages/view', 'Pages', 'view'])]
    #[TestWith(['users-settings/view', 'UsersSettings', 'view'])]
    public function testTemplateName(string $expectedTemplateName, string $controller, string $action): void
    {
        $configuration = new Configuration(TEST_APP, 'TestApp');

        $dispatcher = new readonly class (configuration: $configuration) extends Dispatcher {
            public function templateName(Route $route): string
            {
                return parent::templateName($route);
            }
        };

        $route = new readonly class (controller: $controller, action: $action, namespace: 'TestApp') extends Route {
            public function __construct(
                public string $controller,
                public string $action,
                private string $namespace,
                public array $params = [],
            ) {
            }
        };

        $result = $dispatcher->templateName($route);
        $this->assertSame($expectedTemplateName, $result);
    }
}
