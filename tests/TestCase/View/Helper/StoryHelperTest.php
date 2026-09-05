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
     * @link \App\View\Helper\StoryHelper::image()
     */
    #[Test]
    public function testImageWithLeadingImage(): void
    {
        $result = $this->storyHelper->image(
            "![a tower in the fog](01.jpg)\nThe wind blows hard.",
            'the-tower',
        );

        $this->assertSame(
            '<img src="/assets/stories/the-tower/img/01.jpg" alt="a tower in the fog"'
            . ' class="img-fluid mx-auto mb-5 d-block">',
            $result['html'],
        );
        $this->assertSame('The wind blows hard.', $result['content']);
    }

    /**
     * @link \App\View\Helper\StoryHelper::image()
     */
    #[Test]
    public function testImageWithoutImage(): void
    {
        $result = $this->storyHelper->image('Just plain text, no images.', 'the-tower');

        $this->assertNull($result['html']);
        $this->assertSame('Just plain text, no images.', $result['content']);
    }

    /**
     * The rule is that the image must be the very first thing in the content. An image markdown appearing later
     * isn't extracted here — content is returned exactly as given, so the debugger is the one that catches this
     * as a violation, not this helper silently fixing or ignoring it.
     *
     * @link \App\View\Helper\StoryHelper::image()
     */
    #[Test]
    public function testImageIgnoresImageNotAtTheStart(): void
    {
        $content = "Some text first.\n![too late](99.jpg)\nMore text.";

        $result = $this->storyHelper->image($content, 'the-tower');

        $this->assertNull($result['html']);
        $this->assertSame($content, $result['content']);
    }

    /**
     * @link \App\View\Helper\StoryHelper::image()
     */
    #[Test]
    public function testImageWithOnlyAnImageAndNoFollowingText(): void
    {
        $result = $this->storyHelper->image('![alone](02.jpg)', 'the-tower');

        $this->assertSame(
            '<img src="/assets/stories/the-tower/img/02.jpg" alt="alone" class="img-fluid mx-auto mb-5 d-block">',
            $result['html'],
        );
        $this->assertSame('', $result['content']);
    }

    /**
     * @link \App\View\Helper\StoryHelper::image()
     */
    #[Test]
    public function testImageWithEmptyAltText(): void
    {
        $result = $this->storyHelper->image('![](03.jpg)' . "\n" . 'Text.', 'the-tower');

        $this->assertSame(
            '<img src="/assets/stories/the-tower/img/03.jpg" alt="" class="img-fluid mx-auto mb-5 d-block">',
            $result['html'],
        );
        $this->assertSame('Text.', $result['content']);
    }

    /**
     * @link \App\View\Helper\StoryHelper::image()
     */
    #[Test]
    public function testImageStripsBlankLinesAfterTheImage(): void
    {
        $result = $this->storyHelper->image(
            "![alt](04.jpg)\n\n\nText after several blank lines.",
            'another-story',
        );

        $this->assertSame(
            '<img src="/assets/stories/another-story/img/04.jpg" alt="alt" class="img-fluid mx-auto mb-5 d-block">',
            $result['html'],
        );
        $this->assertSame('Text after several blank lines.', $result['content']);
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
