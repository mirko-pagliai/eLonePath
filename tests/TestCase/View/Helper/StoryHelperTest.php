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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->storyHelper = new StoryHelper(new AppView());
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
}
