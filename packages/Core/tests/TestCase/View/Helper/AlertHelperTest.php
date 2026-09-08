<?php
declare(strict_types=1);

namespace Elone\Core\Test\View\Helper;

use Elone\Core\View\Helper\AlertHelper;
use Elone\Core\View\View;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * AlertHelperTest.
 */
#[CoversClass(AlertHelper::class)]
class AlertHelperTest extends TestCase
{
    private AlertHelper $helper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->helper = new AlertHelper(new View());
    }

    /**
     * Every one of Bootstrap's eight basic variants produces exactly that markup, `role="alert"` included.
     *
     * @link \Elone\Core\View\Helper\AlertHelper::render()
     */
    #[Test]
    #[TestWith(['primary'])]
    #[TestWith(['secondary'])]
    #[TestWith(['success'])]
    #[TestWith(['danger'])]
    #[TestWith(['warning'])]
    #[TestWith(['info'])]
    #[TestWith(['light'])]
    #[TestWith(['dark'])]
    public function testRenderWithEachVariant(string $variant): void
    {
        // @phpstan-ignore-next-line argument.type
        $result = $this->helper->render($variant, 'A simple alert—check it out!');

        $this->assertSame(
            "<div class=\"alert alert-$variant\" role=\"alert\">A simple alert—check it out!</div>",
            $result,
        );
    }

    /**
     * The one call in this file passing something `render()`'s own type doesn't allow, on purpose — proving the
     * runtime guard behind the type actually fires, for the one path (untrusted input, template, this test) that
     * skirts the static check.
     *
     * @link \Elone\Core\View\Helper\AlertHelper::render()
     */
    #[Test]
    public function testRenderWithUnknownVariantThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Unknown alert variant: `boh`.');
        // @phpstan-ignore-next-line argument.type
        $this->helper->render('boh', 'Some message.');
    }

    /**
     * An extra `class` is merged alongside the alert's own two classes, not overwritten — the same convention
     * `HtmlHelper::icon()` already uses for its own `class` option.
     *
     * @link \Elone\Core\View\Helper\AlertHelper::render()
     */
    #[Test]
    public function testRenderWithExtraClass(): void
    {
        $result = $this->helper->render('danger', 'Error.', ['class' => 'mt-3']);

        $this->assertSame('<div class="alert alert-danger mt-3" role="alert">Error.</div>', $result);
    }

    /**
     * Extra attributes beyond `class` are also passed through as plain HTML attributes on the `<div>`.
     *
     * @link \Elone\Core\View\Helper\AlertHelper::render()
     */
    #[Test]
    public function testRenderWithExtraAttribute(): void
    {
        $expected = '<div class="alert alert-info" role="alert" id="my-alert">Alert.</div>';
        $result = $this->helper->render('info', 'Alert.', ['id' => 'my-alert']);

        $this->assertSame($expected, $result);
    }

    /**
     * The message is HTML-escaped — it may come from a translated exception message, not always trusted,
     * developer-written markup.
     *
     * @link \Elone\Core\View\Helper\AlertHelper::render()
     */
    #[Test]
    public function testRenderEscapesMessage(): void
    {
        $result = $this->helper->render('warning', 'A & B');

        $this->assertSame('<div class="alert alert-warning" role="alert">A &amp; B</div>', $result);
    }
}
