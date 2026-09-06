<?php
declare(strict_types=1);

namespace Test\Controller;

use App\Controller\PagesController;
use Elone\Core\Server\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * PagesControllerTest.
 */
#[CoversClass(PagesController::class)]
class PagesControllerTest extends TestCase
{
    /**
     * @link \App\Controller\PagesController::home()
     */
    #[Test]
    public function testHomeTemplateExists(): void
    {
        $controller = new PagesController(new Request('GET', '/'));

        $controller->home();
        $result = $controller->render('Pages/home', layout: null);

        $this->assertNotSame('', trim($result));
    }

    /**
     * @link \App\Controller\PagesController::stories()
     */
    #[Test]
    public function testStoriesTemplateExists(): void
    {
        $controller = new PagesController(new Request('GET', '/'));

        $controller->stories();
        $result = $controller->render('Pages/stories', layout: null);

        $this->assertNotSame('', trim($result));
    }
}
