<?php
declare(strict_types=1);

namespace Elone\Core\Test;

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
     * @link \Elone\Core\Controller::set()
     */
    #[Test]
    public function testSet(): void
    {
        $view = new class extends View {
            public array $data = [];
        };

        $controller = new class (view: $view) extends Controller {
            public readonly View $view;

            public function set(array $data): self
            {
                return parent::set($data);
            }
        };

        $result = $controller->set(['key' => 'value']);
        $this->assertSame($controller, $result);

        $this->assertSame('value', $controller->view->get('key'));
    }
}
