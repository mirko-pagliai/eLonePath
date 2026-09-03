<?php
declare(strict_types=1);

namespace Test\View\Helper;

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
        $this->storyHelper = new StoryHelper();
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

        $this->assertSame('/assets/stories/the-tower/img/01.jpg', $result['path']);
        $this->assertSame('a tower in the fog', $result['alt']);
        $this->assertSame('The wind blows hard.', $result['content']);
    }

    /**
     * @link \App\View\Helper\StoryHelper::image()
     */
    #[Test]
    public function testImageWithoutImage(): void
    {
        $result = $this->storyHelper->image('Just plain text, no images.', 'the-tower');

        $this->assertNull($result['path']);
        $this->assertNull($result['alt']);
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

        $this->assertNull($result['path']);
        $this->assertSame($content, $result['content']);
    }

    /**
     * @link \App\View\Helper\StoryHelper::image()
     */
    #[Test]
    public function testImageWithOnlyAnImageAndNoFollowingText(): void
    {
        $result = $this->storyHelper->image('![alone](02.jpg)', 'the-tower');

        $this->assertSame('', $result['content']);
    }

    /**
     * @link \App\View\Helper\StoryHelper::image()
     */
    #[Test]
    public function testImageWithEmptyAltText(): void
    {
        $result = $this->storyHelper->image('![](03.jpg)' . "\n" . 'Text.', 'the-tower');

        $this->assertSame('', $result['alt']);
        $this->assertSame('/assets/stories/the-tower/img/03.jpg', $result['path']);
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

        $this->assertSame('Text after several blank lines.', $result['content']);
    }
}
