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
    private AlertHelper $alertHelper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->alertHelper = new AlertHelper(new View());
    }

    /**
     * Every one of Bootstrap's eight basic variants — https://getbootstrap.com/docs/5.3/components/alerts/#examples
     * — produces exactly that markup, `role="alert"` included.
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
        $result = $this->alertHelper->render($variant, 'A simple alert—check it out!');

        $this->assertSame(
            "<div class=\"alert alert-$variant\" role=\"alert\">A simple alert—check it out!</div>",
            $result,
        );
    }

    /**
     * @link \Elone\Core\View\Helper\AlertHelper::render()
     */
    #[Test]
    public function testRenderWithUnknownVariantThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Unknown alert variant: `boh`.');
        $this->alertHelper->render('boh', 'Some message.');
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
        $result = $this->alertHelper->render('danger', 'Errore.', ['class' => 'mt-3']);

        $this->assertSame('<div class="alert alert-danger mt-3" role="alert">Errore.</div>', $result);
    }

    /**
     * Extra attributes beyond `class` are also passed through as plain HTML attributes on the `<div>`.
     *
     * @link \Elone\Core\View\Helper\AlertHelper::render()
     */
    #[Test]
    public function testRenderWithExtraAttribute(): void
    {
        $result = $this->alertHelper->render('info', 'Attenzione.', ['id' => 'my-alert']);

        $this->assertSame('<div class="alert alert-info" role="alert" id="my-alert">Attenzione.</div>', $result);
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
        $result = $this->alertHelper->render('warning', 'A & B');

        $this->assertSame('<div class="alert alert-warning" role="alert">A &amp; B</div>', $result);
    }
}
