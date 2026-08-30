<?php
declare(strict_types=1);

namespace Elone\Core\Test;

use Elone\Core\Configuration;
use Elone\Core\Controller;
use Elone\Core\View\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * ControllerTest.
 */
#[CoversClass(Controller::class)]
class ControllerTest extends TestCase
{
    /**
     * @link \Elone\Core\Controller::getConfiguration()
     */
    #[Test]
    public function testGetConfiguration(): void
    {
        $configuration = new Configuration(rootPath: TEST_APP, namespace: 'TestApp');

        $controller = new class (configuration: $configuration) extends Controller {
        };

        $result = $controller->getConfiguration();
        $this->assertSame($configuration, $result);
    }

    /**
     * @link \Elone\Core\Controller::getView()
     */
    #[Test]
    public function testGetView(): void
    {
        $configuration = new Configuration(rootPath: TEST_APP, namespace: 'TestApp');

        $view = new class (configuration: $configuration) extends View {
            public array $data = [];
        };

        $controller = new class (configuration: $configuration, view: $view) extends Controller {
        };

        $result = $controller->getView();
        $this->assertSame($view, $result);
    }

    /**
     * @link \Elone\Core\Controller::set()
     */
    #[Test]
    public function testSet(): void
    {
        $configuration = new Configuration(rootPath: TEST_APP, namespace: 'TestApp');

        $view = new class (configuration: $configuration) extends View {
            public array $data = [];
        };

        $controller = new class (configuration: $configuration, view: $view) extends Controller {
            public function set(array $data): self
            {
                return parent::set($data);
            }
        };

        $result = $controller->set(['key' => 'value']);
        $this->assertSame($controller, $result);

        $this->assertSame('value', $controller->getView()->get('key'));
    }
}
