<?php
declare(strict_types=1);

namespace Elone\Core\Test\View;

use Elone\Core\Exception\HelperNotFoundException;
use Elone\Core\Exception\TemplateNotFoundException;
use Elone\Core\View\Helper\HtmlHelper;
use Elone\Core\View\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * ViewTest.
 */
#[CoversClass(View::class)]
class ViewTest extends TestCase
{
    /**
     * `Html` is loaded the same way any other helper is — through `loadHelper()`, in the constructor — not as a
     * special-cased property. This locks that in.
     *
     * @link \Elone\Core\View\View::__construct()
     */
    #[Test]
    public function testConstructLoadsHtmlHelper(): void
    {
        $view = new View();

        $this->assertInstanceOf(HtmlHelper::class, $view->Html);
    }

    /**
     * @link \Elone\Core\View\View::loadHelper()
     */
    #[Test]
    public function testLoadHelperMakesItAccessible(): void
    {
        $view = new View();
        $helper = new class {
            public function greet(): string
            {
                return 'hi';
            }
        };

        $view->loadHelper('Greeting', $helper);

        $this->assertSame($helper, $view->Greeting);
        $this->assertSame('hi', $view->Greeting->greet());
    }

    /**
     * @link \Elone\Core\View\View::__get()
     */
    #[Test]
    public function testGetWithUnloadedHelperThrows(): void
    {
        $view = new View();

        $this->expectException(HelperNotFoundException::class);
        $this->expectExceptionMessageIs('Helper not loaded: `NotLoaded`.');
        $view->NotLoaded;
    }

    /**
     * @link \Elone\Core\View\View::get()
     */
    #[Test]
    public function testGet(): void
    {
        $view = new View()->set(['key1' => 'value1']);

        $result = $view->get(name: 'noExisting');
        $this->assertSame(null, $result);

        $result = $view->get(name: 'noExisting', default: 'defaultValue');
        $this->assertSame('defaultValue', $result);

        $result = $view->get(name: 'key1');
        $this->assertSame('value1', $result);

        $result = $view->get(name: 'key1', default: 'ignoredDefaultValue');
        $this->assertSame('value1', $result);
    }

    /**
     * @link \Elone\Core\View\View::set()
     */
    #[Test]
    public function testSet(): void
    {
        $view = new View();

        $result = $view->set(['key1' => 'value1', 'key2' => 'value2']);
        $this->assertSame($result, $view);

        $this->assertSame('value1', $view->get('key1'));
        $this->assertSame('value2', $view->get('key2'));

        $view->set(['key2' => 'newValue']);

        $this->assertSame('value1', $view->get('key1'));
        $this->assertSame('newValue', $view->get('key2'));
    }

    /**
     * `pages/home.php` and `layout/default.php` are minimal, generic fixtures — this is what proves `render()`
     * actually resolves and evaluates a real template file, not just something asserted indirectly through
     * `Controller`/`Dispatcher`. The `#test-layout` wrapper in `layout/default.php` is the marker that proves the
     * content genuinely went through the layout.
     *
     * @link \Elone\Core\View\View::render()
     */
    #[Test]
    public function testRenderWithDefaultLayout(): void
    {
        $view = new View();

        $result = $view->render('pages/home');

        $this->assertStringContainsString('Home page.', $result);
        $this->assertStringContainsString('<div id="test-layout">', $result);
    }

    /**
     * `layout: null` skips the layout entirely — the `#test-layout` wrapper from `testRenderWithDefaultLayout()`
     * must not appear here, which is what actually proves the layout was skipped rather than merely empty.
     *
     * @link \Elone\Core\View\View::render()
     */
    #[Test]
    public function testRenderWithoutLayout(): void
    {
        $view = new View();

        $result = $view->render('pages/home', null);

        $this->assertStringContainsString('Home page.', $result);
        $this->assertStringNotContainsString('test-layout', $result);
    }

    /**
     * Test for the `element()` method.
     *
     * `greeting.php` deliberately passes `name` as a data key — the exact key that used to collide with
     * `element()`'s own `$name` parameter before `evaluate()` isolated the extraction scope. This is a regression
     * test for that bug, not just a happy-path check.
     *
     * @link \Elone\Core\View\View::element()
     */
    #[Test]
    public function testElement(): void
    {
        $view = new View();

        $result = $view->element('greeting', ['name' => 'World']);

        $this->assertSame('Hello, World!', trim($result));
    }

    /**
     * @link \Elone\Core\View\View::element()
     */
    #[Test]
    public function testElementDoesNotSeeDataSetOnTheView(): void
    {
        $view = new View()->set(['name' => 'Ignored']);

        $result = $view->element('greeting', ['name' => 'Explicit']);

        $this->assertSame('Hello, Explicit!', trim($result));
    }

    /**
     * @link \Elone\Core\View\View::element()
     */
    #[Test]
    public function testElementWithMissingElement(): void
    {
        $view = new View();

        $this->expectException(TemplateNotFoundException::class);
        $this->expectExceptionMessageIs('Element not found: `no-such-element.php`.');
        $view->element('no-such-element');
    }
}
