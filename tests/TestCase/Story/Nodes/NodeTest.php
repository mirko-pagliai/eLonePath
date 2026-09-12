<?php
declare(strict_types=1);

namespace Test\TestCase\Story\Nodes;

use App\Story\Nodes\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * NodeTest.
 *
 * `Node` is abstract — both methods under test here are `static`, callable directly on the class without
 * instantiating any concrete subclass.
 */
#[CoversClass(Node::class)]
class NodeTest extends TestCase
{
    /**
     * @link \App\Story\Nodes\Node::resolveImagePaths()
     */
    #[Test]
    public function testResolveImagePathsWithNoImages(): void
    {
        $markdown = 'Just plain text, no images.';

        $this->assertSame($markdown, Node::resolveImagePaths($markdown, 'the-tower'));
    }

    /**
     * @link \App\Story\Nodes\Node::resolveImagePaths()
     */
    #[Test]
    public function testResolveImagePathsRewritesThePath(): void
    {
        $result = Node::resolveImagePaths('![A castle](castle.jpg)', 'the-tower');

        $this->assertSame('![A castle](/assets/stories/the-tower/img/castle.jpg)', $result);
    }

    /**
     * Every image gets rewritten, not just the first — text between and around them is left untouched.
     *
     * @link \App\Story\Nodes\Node::resolveImagePaths()
     */
    #[Test]
    public function testResolveImagePathsRewritesEveryImage(): void
    {
        $markdown = '![First](one.jpg)' . "\n" . 'Some text.' . "\n" . '![Second](two.jpg)';

        $result = Node::resolveImagePaths($markdown, 'the-tower');

        $this->assertSame(
            '![First](/assets/stories/the-tower/img/one.jpg)'
            . "\n" . 'Some text.' . "\n"
            . '![Second](/assets/stories/the-tower/img/two.jpg)',
            $result,
        );
    }

    /**
     * An image with empty alt text is still rewritten — the same pattern `findImages()` uses to find it in the
     * first place, so the two never disagree on what counts as an image.
     *
     * @link \App\Story\Nodes\Node::resolveImagePaths()
     */
    #[Test]
    public function testResolveImagePathsWithEmptyAltText(): void
    {
        $result = Node::resolveImagePaths('![](no-alt.jpg)', 'the-tower');

        $this->assertSame('![](/assets/stories/the-tower/img/no-alt.jpg)', $result);
    }

    /**
     * @link \App\Story\Nodes\Node::findImages()
     */
    #[Test]
    public function testFindImagesWithNoImages(): void
    {
        $node = new class (id: 1, gameId: 'some-game', content: 'Just plain text, no images.') extends Node {
            public function toArray(): array
            {
                return [];
            }
        };
        $this->assertSame([], $node->findImages());
    }

    /**
     * @link \App\Story\Nodes\Node::findImages()
     */
    #[Test]
    public function testFindImagesWithOneImage(): void
    {
        $node = new class (
            id: 1,
            gameId: 'some-game',
            content: '![A castle](castle.jpg)' . "\n" . 'Some text.',
        ) extends Node {
            public function toArray(): array
            {
                return [];
            }
        };

        $expected = [
            [
                'alt' => 'A castle',
                'path' => '/assets/stories/some-game/img/castle.jpg',
            ],
        ];
        $result = $node->findImages();
        $this->assertSame($expected, $result);
    }

    /**
     * @link \App\Story\Nodes\Node::findImages()
     */
    #[Test]
    public function testFindImagesWithMultipleImages(): void
    {
        $node = new class (
            id: 1,
            gameId: 'some-game',
            content: '![First](one.jpg)' . "\n" . 'Some text.' . "\n" . '![Second](two.jpg)',
        ) extends Node {
            public function toArray(): array
            {
                return [];
            }
        };

        $expected = [
            [
                'alt' => 'First',
                'path' => '/assets/stories/some-game/img/one.jpg',
            ],
            [
                'alt' => 'Second',
                'path' => '/assets/stories/some-game/img/two.jpg',
            ],
        ];
        $result = $node->findImages();
        $this->assertSame($expected, $result);
    }

    /**
     * Empty alt text still matches — `NodeImagesWalker` relies on finding these to flag them, not on never
     * seeing them.
     *
     * @link \App\Story\Nodes\Node::findImages()
     */
    #[Test]
    public function testFindImagesWithEmptyAltText(): void
    {
        $node = new class (id: 1, gameId: 'some-game', content: '![](no-alt.jpg)') extends Node {
            public function toArray(): array
            {
                return [];
            }
        };

        $expected = [
            [
                'alt' => '',
                'path' => '/assets/stories/some-game/img/no-alt.jpg',
            ],
        ];
        $result = $node->findImages();
        $this->assertSame($expected, $result);
    }
}
