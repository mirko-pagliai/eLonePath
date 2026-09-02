<?php
declare(strict_types=1);

namespace Test\Controller;

use App\Controller\AppController;
use App\View\AppView;
use Elone\Core\View\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * AppControllerTest.
 */
#[CoversClass(AppController::class)]
class AppControllerTest extends TestCase
{
    /**
     * Any controller in this app extending `AppController` — instead of `Elone\Core\Controller` directly — ends up
     * with an `AppView`, without needing to say so itself.
     *
     * @link \App\Controller\AppController::viewClass()
     */
    #[Test]
    public function testViewClassBuildsAppView(): void
    {
        $controller = new class extends AppController {
            public function getView(): View
            {
                return $this->view;
            }
        };

        $this->assertInstanceOf(AppView::class, $controller->getView());
    }
}
