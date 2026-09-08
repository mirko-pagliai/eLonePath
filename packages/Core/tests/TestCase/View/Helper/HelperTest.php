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

        // @phpstan-ignore property.notFound
        $result = $helper->Other;

        $this->assertSame($otherHelper, $result);
    }

    /**
     * @param array<string, bool|float|int|string> $attributes
     * @param string $expected
     *
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
        $helper = new class (new View()) extends Helper {
            public function parseHtmlAttributes(array $attributes): string
            {
                return parent::parseHtmlAttributes($attributes);
            }
        };

        $result = $helper->parseHtmlAttributes($attributes);

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
        $helper = new class (new View()) extends Helper {
            public function parseHtmlAttributes(array $attributes): string
            {
                return parent::parseHtmlAttributes($attributes);
            }
        };

        $result = $helper->parseHtmlAttributes(['title' => 'A "quote" & more']);

        $this->assertSame(' title="A &quot;quote&quot; &amp; more"', $result);
    }

    /**
     * @param array<string|int, string> $route
     * @param string $expected
     *
     * @link \Elone\Core\View\Helper\Helper::url()
     */
    #[Test]
    #[TestWith([['controller' => 'Pages', 'action' => 'home'], '/pages/home'])]
    #[TestWith([['controller' => 'Pages', 'action' => 'view', '123'], '/pages/view/123'])]
    #[TestWith([['controller' => 'UsersSettings'], '/users-settings/index'])]
    public function testUrlWithRoute(array $route, string $expected): void
    {
        $helper = new class (new View()) extends Helper {
        };

        $result = $helper->url($route);

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
        $helper = new class (new View()) extends Helper {
        };

        $result = $helper->url($route);

        $this->assertSame($route, $result);
    }

    /**
     * @link \Elone\Core\View\Helper\Helper::url()
     */
    #[Test]
    public function testUrlWithQuery(): void
    {
        $helper = new class (new View()) extends Helper {
        };

        $result = $helper->url(['controller' => 'Pages', 'action' => 'home'], ['state' => 'abc123']);

        $this->assertSame('/pages/home?state=abc123', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\Helper::url()
     */
    #[Test]
    public function testUrlWithMissingController(): void
    {
        $helper = new class (new View()) extends Helper {
        };

        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessageIs('Invalid route.');
        $helper->url([]);
    }

    /**
     * @link \Elone\Core\View\Helper\Helper::url()
     */
    #[Test]
    public function testUrlWithInvalidParameter(): void
    {
        $helper = new class (new View()) extends Helper {
        };

        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessageIs('Invalid route parameter: `extra`.');
        $helper->url(['controller' => 'Pages', 'action' => 'home', 'extra' => 'value']);
    }
}
