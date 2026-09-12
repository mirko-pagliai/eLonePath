<?php
declare(strict_types=1);

namespace Elone\Core\Test\TestCase\View\Helper;

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
     * @link \Elone\Core\View\Helper\Helper::__construct()
     */
    #[Test]
    public function testConstructStoresConfig(): void
    {
        $helper = new class (new View(), ['key' => 'value']) extends Helper {
            /**
             * @return array<string, mixed>
             */
            public function getConfig(): array
            {
                return $this->config;
            }
        };

        $this->assertSame(['key' => 'value'], $helper->getConfig());
    }

    /**
     * @link \Elone\Core\View\Helper\Helper::__construct()
     */
    #[Test]
    public function testConstructWithNoConfigDefaultsToEmpty(): void
    {
        $helper = new class (new View()) extends Helper {
            /**
             * @return array<string, mixed>
             */
            public function getConfig(): array
            {
                return $this->config;
            }
        };

        $this->assertSame([], $helper->getConfig());
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

        // @phpstan-ignore property.notFound
        $result = $helper->Other;

        $this->assertSame($otherHelper, $result);
    }

    /**
     * @link \Elone\Core\View\Helper\Helper::formatTemplate()
     */
    #[Test]
    public function testFormatTemplateReplacesPlaceholders(): void
    {
        $helper = new class (new View(), [
            'templates' => ['greeting' => 'Hello, {{name}}! You are {{age}}.'],
        ]) extends Helper {
            public function formatTemplate(string $name, array $data): string
            {
                return parent::formatTemplate($name, $data);
            }
        };

        $result = $helper->formatTemplate('greeting', ['name' => 'Ada', 'age' => '36']);

        $this->assertSame('Hello, Ada! You are 36.', $result);
    }

    /**
     * A placeholder `$data` has no entry for is replaced with an empty string, not left as `{{...}}` in the
     * output.
     *
     * @link \Elone\Core\View\Helper\Helper::formatTemplate()
     */
    #[Test]
    public function testFormatTemplateWithMissingDataKeyUsesEmptyString(): void
    {
        $helper = new class (new View(), ['templates' => ['greeting' => 'Hello, {{name}}!{{suffix}}']]) extends Helper {
            public function formatTemplate(string $name, array $data): string
            {
                return parent::formatTemplate($name, $data);
            }
        };

        $result = $helper->formatTemplate('greeting', ['name' => 'Ada']);

        $this->assertSame('Hello, Ada!', $result);
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
