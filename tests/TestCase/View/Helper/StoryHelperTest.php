<?php
declare(strict_types=1);

namespace Test\View\Helper;

use App\View\AppView;
use App\View\Helper\StoryHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * StoryHelperTest.
 */
#[CoversClass(StoryHelper::class)]
class StoryHelperTest extends TestCase
{
    private StoryHelper $storyHelper;

    private AppView $view;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->view = new AppView();
        $this->storyHelper = new StoryHelper($this->view);
    }

    /**
     * With no `state` ever `set()` on the view, `link()` behaves exactly like a plain `Html->link()` call — no
     * `?state=` appears out of nowhere.
     *
     * @link \App\View\Helper\StoryHelper::link()
     */
    #[Test]
    public function testLinkWithoutStateBehavesLikeHtmlLink(): void
    {
        $result = $this->storyHelper->link('Continua', ['controller' => 'Pages', 'action' => 'home']);

        $this->assertSame('<a href="/pages/home">Continua</a>', $result);
    }

    /**
     * The behavior this helper exists for: once the view has a `state` value (as `StoryController::propagateState()`
     * `set()`s it), every link built through here carries it forward automatically — the template never has to
     * pass `query: ['state' => ...]` itself.
     *
     * @link \App\View\Helper\StoryHelper::link()
     */
    #[Test]
    public function testLinkCarriesStateForward(): void
    {
        $this->view->set(['state' => 'abc123']);

        $result = $this->storyHelper->link('Continua', ['controller' => 'Pages', 'action' => 'home']);

        $this->assertSame('<a href="/pages/home?state=abc123">Continua</a>', $result);
    }

    /**
     * An explicit `query` key is merged alongside the state, not replaced by it — this is what proves the two
     * coexist rather than one silently winning by default.
     *
     * @link \App\View\Helper\StoryHelper::link()
     */
    #[Test]
    public function testLinkMergesStateWithExplicitQuery(): void
    {
        $this->view->set(['state' => 'abc123']);

        $result = $this->storyHelper->link(
            'Continua',
            ['controller' => 'Pages', 'action' => 'home'],
            query: ['foo' => 'bar'],
        );

        $this->assertSame('<a href="/pages/home?state=abc123&amp;foo=bar">Continua</a>', $result);
    }

    /**
     * An explicit `state` key in `$query` wins over whatever the view itself holds — the caller asked for a
     * specific value on purpose.
     *
     * @link \App\View\Helper\StoryHelper::link()
     */
    #[Test]
    public function testLinkExplicitStateOverridesTheViewsOwn(): void
    {
        $this->view->set(['state' => 'from-view']);

        $result = $this->storyHelper->link(
            'Continua',
            ['controller' => 'Pages', 'action' => 'home'],
            query: ['state' => 'explicit'],
        );

        $this->assertSame('<a href="/pages/home?state=explicit">Continua</a>', $result);
    }

    /**
     * A non-string `state` (e.g. `null`, since nothing ever `set()` one, or a stray non-string value) is treated
     * the same as no state at all — never coerced into the URL.
     *
     * @link \App\View\Helper\StoryHelper::link()
     */
    #[Test]
    public function testLinkIgnoresNonStringState(): void
    {
        $this->view->set(['state' => null]);

        $result = $this->storyHelper->link('Continua', ['controller' => 'Pages', 'action' => 'home']);

        $this->assertSame('<a href="/pages/home">Continua</a>', $result);
    }

    /**
     * `options`/`escape` still behave exactly as they do on `Html->link()` — this helper only changes what
     * happens to `query`, nothing else about the underlying call.
     *
     * @link \App\View\Helper\StoryHelper::link()
     */
    #[Test]
    public function testLinkPassesThroughOptions(): void
    {
        $this->view->set(['state' => 'abc123']);

        $result = $this->storyHelper->link(
            '<strong>Bold</strong>',
            ['controller' => 'Pages', 'action' => 'home'],
            options: ['class' => 'btn', 'escape' => false],
        );

        $this->assertSame('<a href="/pages/home?state=abc123" class="btn"><strong>Bold</strong></a>', $result);
    }
}
