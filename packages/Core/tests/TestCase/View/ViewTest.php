<?php
declare(strict_types=1);

namespace Elone\Core\Test\TestCase\View;

use Elone\Core\Exception\HelperNotFoundException;
use Elone\Core\Exception\TemplateNotFoundException;
use Elone\Core\Server\Request;
use Elone\Core\View\Helper\Helper;
use Elone\Core\View\View;
use LogicException;
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
     * @link \Elone\Core\View\View::__construct()
     * @link \Elone\Core\View\View::getRequest()
     */
    #[Test]
    public function testGetRequestReturnsTheRequestPassedToTheConstructor(): void
    {
        $request = new Request('GET', '/');
        $view = new View($request);

        $this->assertSame($request, $view->getRequest());
    }

    /**
     * A `View` built with no request at all — `ErrorHandler`'s own, for instance — has none to return.
     *
     * @link \Elone\Core\View\View::__construct()
     * @link \Elone\Core\View\View::getRequest()
     */
    #[Test]
    public function testGetRequestWithNoRequestReturnsNull(): void
    {
        $view = new View();

        $this->assertNull($view->getRequest());
    }

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
     * template itself calls (reading `get('state')`) could never see anything `set()` for that same render —
     * `get()` always returned `null` regardless of what had just been `set()`. `probe.php` calls a helper that
     * reads `get('state')` from inside the very render that `set()` it, which is exactly the path that used to
     * fail.
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
                $state = $this->view->get('state', 'MISSING');

                return is_string($state) ? $state : 'MISSING';
            }
        };
        $view->loadHelper('Probe', $helper);
        $view->set(['state' => 'abc123']);

        /** @link packages/Core/tests/test_app/templates/Pages/probe.php */
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
     * `start()`/`end()` capture whatever is echoed between them into the named block — readable back with
     * `fetch()`, independent of any template rendering.
     *
     * @link \Elone\Core\View\View::start()
     * @link \Elone\Core\View\View::end()
     * @link \Elone\Core\View\View::fetch()
     */
    #[Test]
    public function testStartEndCapturesIntoNamedBlock(): void
    {
        $view = new View();

        $view->start('greeting');
        echo 'captured text';
        $view->end();

        $this->assertSame('captured text', $view->fetch('greeting'));
    }

    /**
     * A second `start()`/`end()` pair on the same block replaces its content — `start()` isn't cumulative on
     * its own; `append()`/`prepend()` are what add to an existing block.
     *
     * @link \Elone\Core\View\View::start()
     * @link \Elone\Core\View\View::end()
     */
    #[Test]
    public function testStartEndReplacesExistingBlockContent(): void
    {
        $view = new View();

        $view->start('greeting');
        echo 'first';
        $view->end();

        $view->start('greeting');
        echo 'second';
        $view->end();

        $this->assertSame('second', $view->fetch('greeting'));
    }

    /**
     * @link \Elone\Core\View\View::assign()
     */
    #[Test]
    public function testAssignSetsBlockDirectly(): void
    {
        $view = new View();

        $view->assign('title', 'Page Title');

        $this->assertSame('Page Title', $view->fetch('title'));
    }

    /**
     * @link \Elone\Core\View\View::assign()
     */
    #[Test]
    public function testAssignReplacesExistingBlockContent(): void
    {
        $view = new View();

        $view->assign('title', 'First');
        $view->assign('title', 'Second');

        $this->assertSame('Second', $view->fetch('title'));
    }

    /**
     * With a value given directly, `append()` adds it to the block without capturing anything.
     *
     * @link \Elone\Core\View\View::append()
     */
    #[Test]
    public function testAppendWithValueAppendsDirectly(): void
    {
        $view = new View();
        $view->assign('list', 'A');

        $view->append('list', 'B');

        $this->assertSame('AB', $view->fetch('list'));
    }

    /**
     * With no value, `append()` starts capturing instead — the matching `end()` call adds whatever was echoed
     * after whatever the block already held.
     *
     * @link \Elone\Core\View\View::append()
     */
    #[Test]
    public function testAppendWithoutValueCapturesAndAppends(): void
    {
        $view = new View();
        $view->assign('list', 'A');

        $view->append('list');
        echo 'B';
        $view->end();

        $this->assertSame('AB', $view->fetch('list'));
    }

    /**
     * `append()` on a block that doesn't exist yet behaves like `assign()` — nothing to append after.
     *
     * @link \Elone\Core\View\View::append()
     */
    #[Test]
    public function testAppendWithValueOnMissingBlockActsLikeAssign(): void
    {
        $view = new View();

        $view->append('list', 'only');

        $this->assertSame('only', $view->fetch('list'));
    }

    /**
     * With a value given directly, `prepend()` adds it before the block without capturing anything.
     *
     * @link \Elone\Core\View\View::prepend()
     */
    #[Test]
    public function testPrependWithValuePrependsDirectly(): void
    {
        $view = new View();
        $view->assign('list', 'B');

        $view->prepend('list', 'A');

        $this->assertSame('AB', $view->fetch('list'));
    }

    /**
     * With no value, `prepend()` starts capturing instead — the matching `end()` call adds whatever was echoed
     * before whatever the block already held.
     *
     * @link \Elone\Core\View\View::prepend()
     */
    #[Test]
    public function testPrependWithoutValueCapturesAndPrepends(): void
    {
        $view = new View();
        $view->assign('list', 'B');

        $view->prepend('list');
        echo 'A';
        $view->end();

        $this->assertSame('AB', $view->fetch('list'));
    }

    /**
     * @link \Elone\Core\View\View::reset()
     */
    #[Test]
    public function testResetClearsBlock(): void
    {
        $view = new View();
        $view->assign('title', 'Something');

        $view->reset('title');

        $this->assertSame('', $view->fetch('title'));
    }

    /**
     * `reset()` on a block that was never set doesn't throw — nothing to clear either way.
     *
     * @link \Elone\Core\View\View::reset()
     */
    #[Test]
    public function testResetOnMissingBlockDoesNothing(): void
    {
        $view = new View();

        $view->reset('never-set');

        $this->assertSame('', $view->fetch('never-set'));
    }

    /**
     * @link \Elone\Core\View\View::fetch()
     */
    #[Test]
    public function testFetchWithMissingBlockReturnsEmptyStringByDefault(): void
    {
        $view = new View();

        $this->assertSame('', $view->fetch('never-set'));
    }

    /**
     * @link \Elone\Core\View\View::fetch()
     */
    #[Test]
    public function testFetchWithMissingBlockReturnsGivenDefault(): void
    {
        $view = new View();

        $this->assertSame('Fallback', $view->fetch('never-set', 'Fallback'));
    }

    /**
     * @link \Elone\Core\View\View::end()
     */
    #[Test]
    public function testEndWithNoOpenCaptureThrows(): void
    {
        $view = new View();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIs('end() called with no matching start(), append(), or prepend() open.');
        $view->end();
    }

    /**
     * A template that calls `extend()` hands its own rendering off to the named parent — the parent's own
     * output is what `render()` ultimately returns, with the extending template's output available to it as
     * the `content` block.
     *
     * @link \Elone\Core\View\View::extend()
     * @link \Elone\Core\View\View::render()
     */
    #[Test]
    public function testExtendRendersTheParentWithContentBlock(): void
    {
        $view = new View();

        /** @link packages/Core/tests/test_app/templates/Pages/extend-simple.php */
        /** @link packages/Core/tests/test_app/templates/common/panel.php */
        $result = $view->render('Pages/extend-simple', null);

        $this->assertStringContainsString('Simple extended body.', $result);
    }

    /**
     * Every block a template defines before (or instead of) letting its own output become `content` — here,
     * `title` via `assign()` and `sidebar` via `start()`/`end()` — is still readable by the parent it
     * `extend()`s to.
     *
     * @link \Elone\Core\View\View::extend()
     * @link \Elone\Core\View\View::render()
     */
    #[Test]
    public function testExtendLetsParentReadBlocksTheChildDefined(): void
    {
        $view = new View();

        /** @link packages/Core/tests/test_app/templates/Pages/extend-with-blocks.php */
        /** @link packages/Core/tests/test_app/templates/common/panel.php */
        $result = $view->render('Pages/extend-with-blocks', null);

        $this->assertStringContainsString('<h1>Extended Title</h1>', $result);
        $this->assertStringContainsString('Body text.', $result);
        $this->assertStringContainsString('<aside>Sidebar text.</aside>', $result);
    }

    /**
     * `extend()` chains: a template extending to a parent that itself calls `extend()` again is followed all
     * the way to the end, each step's own output becoming the next step's `content`.
     *
     * @link \Elone\Core\View\View::extend()
     * @link \Elone\Core\View\View::render()
     */
    #[Test]
    public function testExtendChainsThroughMultipleLevels(): void
    {
        $view = new View();

        /** @link packages/Core/tests/test_app/templates/Pages/extend-chained.php */
        /** @link packages/Core/tests/test_app/templates/common/chain-inner.php */
        /** @link packages/Core/tests/test_app/templates/common/chain-outer.php */
        $result = $view->render('Pages/extend-chained', null);

        $this->assertStringContainsString('[OUTER]', $result);
        $this->assertStringContainsString('[INNER]', $result);
        $this->assertStringContainsString('Leaf.', $result);
        $this->assertStringContainsString('[/INNER]', $result);
        $this->assertStringContainsString('[/OUTER]', $result);
        $this->assertLessThan(strpos($result, '[/OUTER]'), strpos($result, '[/INNER]'));
    }

    /**
     * A layout can `fetch('content')` to get the rendered template's own output, the same way it can already
     * read it as a plain `$content` variable — this doesn't require the template itself to have called
     * `extend()` at all.
     *
     * @link \Elone\Core\View\View::render()
     * @link \Elone\Core\View\View::fetch()
     */
    #[Test]
    public function testLayoutCanFetchContentWithoutTheTemplateExtendingAnything(): void
    {
        $view = new View();

        /** @link packages/Core/tests/test_app/templates/Pages/home.php */
        /** @link packages/Core/tests/test_app/templates/layout/default.php */
        $result = $view->render('Pages/home');

        $this->assertStringContainsString('Home page.', $result);
    }

    /**
     * Every block set during one `render()` call — directly, or via a followed `extend()` chain — is gone by
     * the time the next, separate `render()` call on the same `View` instance runs; the same way `$this->data`
     * is cleared between calls.
     *
     * @link \Elone\Core\View\View::render()
     */
    #[Test]
    public function testRenderClearsBlocksAfterEvaluation(): void
    {
        $view = new View();
        $view->render('Pages/extend-with-blocks', null);

        $result = $view->render('Pages/home', null);

        $this->assertStringNotContainsString('Sidebar text.', $result);
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
