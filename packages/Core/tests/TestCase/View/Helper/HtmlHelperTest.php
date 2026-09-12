<?php
declare(strict_types=1);

namespace Elone\Core\Test\TestCase\View\Helper;

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
    private HtmlHelper $helper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->helper = new HtmlHelper(new View());
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
        $result = $this->helper->icon($name);
        $this->assertSame('<i class="bi bi-github"></i>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::icon()
     */
    #[Test]
    public function testIconWithOptions(): void
    {
        $result = $this->helper->icon('github', ['class' => 'fs-3', 'aria-hidden' => 'true']);
        $this->assertSame('<i class="bi bi-github fs-3" aria-hidden="true"></i>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::image()
     */
    #[Test]
    public function testImage(): void
    {
        $result = $this->helper->image('/assets/img/example/1.jpg');
        $this->assertSame('<img src="/assets/img/example/1.jpg">', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::image()
     */
    #[Test]
    public function testImageWithOptions(): void
    {
        $expected = '<img src="/img.jpg" alt="A &quot;special&quot; image" class="img-fluid">';
        $result = $this->helper->image('/img.jpg', ['alt' => 'A "special" image', 'class' => 'img-fluid']);
        $this->assertSame($expected, $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::link()
     */
    #[Test]
    public function testLink(): void
    {
        $result = $this->helper->link('Home', ['controller' => 'Pages', 'action' => 'home']);
        $this->assertSame('<a href="/pages/home">Home</a>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::link()
     */
    #[Test]
    public function testLinkEscapesTextAndAttributesByDefault(): void
    {
        $expected = '<a href="/pages/home" class="btn &quot;special&quot;">A &amp; B</a>';
        $result = $this->helper->link(
            'A & B',
            ['controller' => 'Pages', 'action' => 'home'],
            ['class' => 'btn "special"'],
        );

        $this->assertSame($expected, $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::link()
     */
    #[Test]
    public function testLinkWithEscapeFalseRendersRawText(): void
    {
        $expected = '<a href="/pages/home"><strong>Bold</strong></a>';
        $result = $this->helper->link(
            '<strong>Bold</strong>',
            ['controller' => 'Pages', 'action' => 'home'],
            ['escape' => false],
        );

        $this->assertSame($expected, $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::link()
     */
    #[Test]
    public function testLinkEscapeOptionIsNotRenderedAsAttribute(): void
    {
        $result = $this->helper->link(
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
        $result = $this->helper->link('Home', '/');
        $this->assertSame('<a href="/">Home</a>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::link()
     */
    #[Test]
    public function testLinkWithExternalUrlString(): void
    {
        $result = $this->helper->link('Example', 'https://example.com');
        $this->assertSame('<a href="https://example.com">Example</a>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::link()
     */
    #[Test]
    public function testLinkWithIcon(): void
    {
        $result = $this->helper->link('Home', '/', ['icon' => 'house', 'class' => 'nav-link']);
        $this->assertSame('<a href="/" class="nav-link"><i class="bi bi-house"></i> Home</a>', $result);
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
        $result = $this->helper->link(
            'Go',
            ['controller' => 'Pages', 'action' => 'view', '123'],
            query: ['ref' => 'abc123'],
        );
        $this->assertSame('<a href="/pages/view/123?ref=abc123">Go</a>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::markdown()
     */
    #[Test]
    #[TestWith(['This is a normal string', '<p>This is a normal string</p>'])]
    #[TestWith(['This is a **bold** string', '<p>This is a <strong>bold</strong> string</p>'])]
    #[TestWith(['', ''])]
    public function testMarkdown(string $string, string $expected): void
    {
        $result = $this->helper->markdown($string);
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
     * @link \Elone\Core\View\Helper\HtmlHelper::tag()
     */
    #[Test]
    public function testTag(): void
    {
        $result = $this->helper->tag('span', 'Badge');
        $this->assertSame('<span>Badge</span>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::tag()
     */
    #[Test]
    public function testTagWithOptions(): void
    {
        $result = $this->helper->tag('span', 'Badge', ['class' => 'badge text-bg-primary']);
        $this->assertSame('<span class="badge text-bg-primary">Badge</span>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::tag()
     */
    #[Test]
    public function testTagEscapesTextByDefault(): void
    {
        $result = $this->helper->tag('span', 'A & B');
        $this->assertSame('<span>A &amp; B</span>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::tag()
     */
    #[Test]
    public function testTagWithEscapeFalseRendersRawText(): void
    {
        $result = $this->helper->tag('span', '<strong>Bold</strong>', ['escape' => false]);
        $this->assertSame('<span><strong>Bold</strong></span>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::tag()
     */
    #[Test]
    public function testTagWithIcon(): void
    {
        $result = $this->helper->tag('span', 'Warning', ['icon' => 'exclamation-triangle']);
        $this->assertSame('<span><i class="bi bi-exclamation-triangle"></i> Warning</span>', $result);
    }
}
