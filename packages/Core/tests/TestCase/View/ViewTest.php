<?php
declare(strict_types=1);

namespace Elone\Core\Test\View;

use Elone\Core\Exception\HelperNotFoundException;
use Elone\Core\Exception\TemplateNotFoundException;
use Elone\Core\View\Helper\Helper;
use Elone\Core\View\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * ViewTest.
 */
#[CoversClass(View::class)]
class ViewTest extends TestCase
{
    /**
     * Loading a real `Helper` subclass makes it accessible under the name it was loaded as. Calls `__get()`
     * explicitly rather than through the magic `$view->Greeting` syntax — the two are identical at runtime, but
     * `Greeting` isn't a real helper this app ever loads, so there's no `@property` declaring it; going through
     * the method directly is what PHPStan can actually verify against `__get()`'s own signature.
     *
     * @link \Elone\Core\View\View::loadHelper()
     */
    #[Test]
    public function testLoadHelperMakesItAccessible(): void
    {
        $view = new View();
        $helper = new class ($view) extends Helper {
            public function greet(): string
            {
                return 'hi';
            }
        };

        $view->loadHelper('Greeting', $helper);

        $this->assertSame($helper, $view->__get('Greeting'));
    }

    /**
     * `loadHelper()` only accepts a `Helper` subclass — the parameter type itself enforces this, so passing
     * anything else is a `TypeError`, not a runtime check this class has to perform.
     *
     * @link \Elone\Core\View\View::loadHelper()
     */
    #[Test]
    public function testLoadHelperRejectsNonHelperObjects(): void
    {
        $view = new View();
        $notAHelper = new class {
            public function foo(): string
            {
                return 'bar';
            }
        };

        $this->expectException(TypeError::class);
        // @phpstan-ignore-next-line argument.type
        $view->loadHelper('Bad', $notAHelper);
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
        $view->__get('NotLoaded');
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
     * `Pages/home.php` and `layout/default.php` are minimal, generic fixtures — this is what proves `render()`
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

        $result = $view->render('Pages/home');

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

        $result = $view->render('Pages/home', null);

        $this->assertStringContainsString('Home page.', $result);
        $this->assertStringNotContainsString('test-layout', $result);
    }

    /**
     * Regression test: `$this->data` used to get cleared *before* the template was evaluated, so a helper the
     * template itself calls (e.g. `App\View\Helper\StoryHelper::link()`, reading `get('state')`) could never see
     * anything `set()` for that same render — `get()` always returned `null` regardless of what had just been
     * `set()`. `probe.php` calls a helper that reads `get('state')` from inside the very render that `set()` it,
     * which is exactly the path that used to fail.
     *
     * @link \Elone\Core\View\View::render()
     * @link \Elone\Core\View\View::get()
     */
    #[Test]
    public function testRenderKeepsDataAvailableToHelpersDuringEvaluation(): void
    {
        $view = new View();
        $helper = new class ($view) extends Helper {
            public function readState(): string
            {
                return (string)$this->view->get('state', 'MISSING');
            }
        };
        $view->loadHelper('Probe', $helper);
        $view->set(['state' => 'abc123']);

        $result = $view->render('Pages/probe', null);

        $this->assertSame('abc123', trim($result));
    }

    /**
     * The other half of the same behavior: once the template is done, `$this->data` is still cleared, ready for
     * whatever the next `render()` call `set()`s — this is what proves the fix only changed *when* the clearing
     * happens, not whether it happens at all.
     *
     * @link \Elone\Core\View\View::render()
     */
    #[Test]
    public function testRenderStillClearsDataAfterEvaluation(): void
    {
        $view = new View();
        $view->set(['key1' => 'value1']);

        $view->render('Pages/home');

        $this->assertNull($view->get('key1'));
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
