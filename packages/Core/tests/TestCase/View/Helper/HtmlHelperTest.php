<?php
declare(strict_types=1);

namespace Elone\Core\Test\View\Helper;

use Elone\Core\Exception\RouteNotFoundException;
use Elone\Core\View\Helper\HtmlHelper;
use Elone\Core\View\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * HtmlHelperTest.
 */
#[CoversClass(HtmlHelper::class)]
class HtmlHelperTest extends TestCase
{
    private HtmlHelper $htmlHelper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->htmlHelper = new HtmlHelper(new View());
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::icon()
     */
    #[Test]
    #[TestWith(['github'])]
    #[TestWith(['bi-github'])]
    #[TestWith(['bi bi-github'])]
    public function testIcon(string $name): void
    {
        $result = $this->htmlHelper->icon($name);
        $this->assertSame('<i class="bi bi-github"></i>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::icon()
     */
    #[Test]
    public function testIconWithOptions(): void
    {
        $result = $this->htmlHelper->icon('github', ['class' => 'fs-3', 'aria-hidden' => 'true']);
        $this->assertSame('<i class="bi bi-github fs-3" aria-hidden="true"></i>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::image()
     */
    #[Test]
    public function testImage(): void
    {
        $result = $this->htmlHelper->image('/assets/img/stories/example/1.jpg');
        $this->assertSame('<img src="/assets/img/stories/example/1.jpg">', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::image()
     */
    #[Test]
    public function testImageWithOptions(): void
    {
        $result = $this->htmlHelper->image('/img.jpg', ['alt' => 'A "special" image', 'class' => 'img-fluid']);
        $this->assertSame('<img src="/img.jpg" alt="A &quot;special&quot; image" class="img-fluid">', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::link()
     */
    #[Test]
    public function testLink(): void
    {
        $result = $this->htmlHelper->link('Home', ['controller' => 'Pages', 'action' => 'home']);
        $this->assertSame('<a href="/pages/home">Home</a>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::link()
     */
    #[Test]
    public function testLinkEscapesTextAndAttributesByDefault(): void
    {
        $result = $this->htmlHelper->link(
            'A & B',
            ['controller' => 'Pages', 'action' => 'home'],
            ['class' => 'btn "special"'],
        );
        $this->assertSame('<a href="/pages/home" class="btn &quot;special&quot;">A &amp; B</a>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::link()
     */
    #[Test]
    public function testLinkWithEscapeFalseRendersRawText(): void
    {
        $result = $this->htmlHelper->link(
            '<strong>Bold</strong>',
            ['controller' => 'Pages', 'action' => 'home'],
            ['escape' => false],
        );
        $this->assertSame('<a href="/pages/home"><strong>Bold</strong></a>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::link()
     */
    #[Test]
    public function testLinkEscapeOptionIsNotRenderedAsAttribute(): void
    {
        $result = $this->htmlHelper->link(
            'Home',
            ['controller' => 'Pages', 'action' => 'home'],
            ['escape' => true, 'class' => 'btn'],
        );
        $this->assertSame('<a href="/pages/home" class="btn">Home</a>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::link()
     */
    #[Test]
    public function testLinkWithStringUrl(): void
    {
        $result = $this->htmlHelper->link('Home', '/');
        $this->assertSame('<a href="/">Home</a>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::link()
     */
    #[Test]
    public function testLinkWithExternalUrlString(): void
    {
        $result = $this->htmlHelper->link('Example', 'https://example.com');
        $this->assertSame('<a href="https://example.com">Example</a>', $result);
    }

    /**
     * `$query` on `link()` carries through to the underlying `url()`/`Route::resolve()` call, appended to the
     * `href` — separate from `$options`, which only ever becomes HTML attributes.
     *
     * @link \Elone\Core\View\Helper\HtmlHelper::link()
     */
    #[Test]
    public function testLinkWithQuery(): void
    {
        $result = $this->htmlHelper->link(
            'Continua',
            ['controller' => 'Story', 'action' => 'chapter', 'the-tower', '5'],
            query: ['state' => 'abc123'],
        );
        $this->assertSame('<a href="/story/chapter/the-tower/5?state=abc123">Continua</a>', $result);
    }

    /**
     * Tests for the `markdown()` method.
     *
     * @link \Elone\Core\View\Helper\HtmlHelper::markdown()
     */
    #[Test]
    #[TestWith(['This is a normal string', '<p>This is a normal string</p>'])]
    #[TestWith(['This is a **bold** string', '<p>This is a <strong>bold</strong> string</p>'])]
    #[TestWith(['', ''])]
    public function testMarkdown(string $string, string $expected): void
    {
        $result = $this->htmlHelper->markdown($string);
        $this->assertSame($expected, trim($result));
    }

    /**
     * Test for the `markdown()` method when the `michelf/php-markdown` package is missing.
     *
     * @link \Elone\Core\View\Helper\HtmlHelper::markdown()
     */
    #[Test]
    public function testMarkdownPackageIsMissing(): void
    {
        $htmlHelper = new class (new View()) extends HtmlHelper {
            protected function checkHasMarkdown(): bool
            {
                return false;
            }
        };

        $this->expectExceptionMessageIs(
            'Package `michelf/php-markdown` is required to use `' . HtmlHelper::class . '::markdown()`.',
        );
        $htmlHelper->markdown('');
    }

    /**
     * @param array<string|int, string|int|float|bool> $route
     *
     * @link \Elone\Core\View\Helper\HtmlHelper::url()
     */
    #[Test]
    #[TestWith([['controller' => 'Pages', 'action' => 'home'], '/pages/home'])]
    #[TestWith([['controller' => 'Pages', 'action' => 'view', '123'], '/pages/view/123'])]
    #[TestWith([['controller' => 'UsersSettings'], '/users-settings/index'])]
    public function testUrl(array $route, string $expected): void
    {
        $result = $this->htmlHelper->url($route);
        $this->assertSame($expected, $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::url()
     */
    #[Test]
    #[TestWith(['/'])]
    #[TestWith(['/img/logo-1024.png'])]
    #[TestWith(['https://example.com/path'])]
    public function testUrlWithString(string $route): void
    {
        $result = $this->htmlHelper->url($route);
        $this->assertSame($route, $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::url()
     */
    #[Test]
    public function testUrlWithQuery(): void
    {
        $result = $this->htmlHelper->url(['controller' => 'Pages', 'action' => 'home'], ['state' => 'abc123']);
        $this->assertSame('/pages/home?state=abc123', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::url()
     */
    #[Test]
    public function testUrlWithMissingController(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessageIs('Invalid route.');
        $this->htmlHelper->url([]);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::url()
     */
    #[Test]
    public function testUrlWithInvalidParameter(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessageIs('Invalid route parameter: `extra`.');
        $this->htmlHelper->url(['controller' => 'Pages', 'action' => 'home', 'extra' => 'value']);
    }
}
