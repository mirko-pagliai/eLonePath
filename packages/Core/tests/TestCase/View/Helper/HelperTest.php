<?php
declare(strict_types=1);

namespace Elone\Core\Test\View\Helper;

use Elone\Core\Exception\RouteNotFoundException;
use Elone\Core\View\Helper\Helper;
use Elone\Core\View\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * HelperTest.
 */
#[CoversClass(Helper::class)]
class HelperTest extends TestCase
{
    /**
     * A minimal concrete `Helper` exposing `parseHtmlAttributes()` (`protected`) for testing.
     */
    private function makeHelper(): Helper
    {
        return new class (new View()) extends Helper {
            public function parseHtmlAttributes(array $attributes): string
            {
                return parent::parseHtmlAttributes($attributes);
            }
        };
    }

    /**
     * @link \Elone\Core\View\Helper\Helper::__get()
     */
    #[Test]
    public function testGetDelegatesToTheView(): void
    {
        $view = new View();
        $otherHelper = new class ($view) extends Helper {
        };
        $view->loadHelper('Other', $otherHelper);

        $helper = new class ($view) extends Helper {
        };

        $this->assertSame($otherHelper, $helper->Other);
    }

    /**
     * @link \Elone\Core\View\Helper\Helper::parseHtmlAttributes()
     */
    #[Test]
    #[TestWith([[], ''])]
    #[TestWith([['class' => 'btn'], ' class="btn"'])]
    #[TestWith([['class' => 'btn', 'id' => 'x'], ' class="btn" id="x"'])]
    #[TestWith([['data-count' => 3], ' data-count="3"'])]
    #[TestWith([['required' => true], ' required="1"'])]
    public function testParseHtmlAttributes(array $attributes, string $expected): void
    {
        $result = $this->makeHelper()->parseHtmlAttributes($attributes);

        $this->assertSame($expected, $result);
    }

    /**
     * Both the attribute name and its value are escaped — this is what protects against either one containing
     * something that would otherwise break out of the attribute's own quotes.
     *
     * @link \Elone\Core\View\Helper\Helper::parseHtmlAttributes()
     */
    #[Test]
    public function testParseHtmlAttributesEscapesValues(): void
    {
        $result = $this->makeHelper()->parseHtmlAttributes(['title' => 'A "quote" & more']);

        $this->assertSame(' title="A &quot;quote&quot; &amp; more"', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\Helper::url()
     */
    #[Test]
    #[TestWith([['controller' => 'Pages', 'action' => 'home'], '/pages/home'])]
    #[TestWith([['controller' => 'Pages', 'action' => 'view', '123'], '/pages/view/123'])]
    #[TestWith([['controller' => 'UsersSettings'], '/users-settings/index'])]
    public function testUrlWithRoute(array $route, string $expected): void
    {
        $result = $this->makeHelper()->url($route);

        $this->assertSame($expected, $result);
    }

    /**
     * @link \Elone\Core\View\Helper\Helper::url()
     */
    #[Test]
    #[TestWith(['/'])]
    #[TestWith(['/img/logo-1024.png'])]
    #[TestWith(['https://example.com/path'])]
    public function testUrlWithString(string $route): void
    {
        $result = $this->makeHelper()->url($route);

        $this->assertSame($route, $result);
    }

    /**
     * @link \Elone\Core\View\Helper\Helper::url()
     */
    #[Test]
    public function testUrlWithQuery(): void
    {
        $result = $this->makeHelper()->url(['controller' => 'Pages', 'action' => 'home'], ['state' => 'abc123']);

        $this->assertSame('/pages/home?state=abc123', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\Helper::url()
     */
    #[Test]
    public function testUrlWithMissingController(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessageIs('Invalid route.');
        $this->makeHelper()->url([]);
    }

    /**
     * @link \Elone\Core\View\Helper\Helper::url()
     */
    #[Test]
    public function testUrlWithInvalidParameter(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessageIs('Invalid route parameter: `extra`.');
        $this->makeHelper()->url(['controller' => 'Pages', 'action' => 'home', 'extra' => 'value']);
    }
}
