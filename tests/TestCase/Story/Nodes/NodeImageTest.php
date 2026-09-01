<?php
declare(strict_types=1);

namespace Test\Story\Nodes;

use App\Story\Nodes\NodeImage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * NodeImageTest.
 */
#[CoversClass(NodeImage::class)]
class NodeImageTest extends TestCase
{
    /**
     * @link \App\Story\Nodes\NodeImage::__construct()
     */
    #[Test]
    public function testConstruct(): void
    {
        $image = new NodeImage(path: '11.jpg', title: 'Cover art');

        $this->assertSame('11.jpg', $image->path);
        $this->assertSame('Cover art', $image->title);
    }

    /**
     * @link \App\Story\Nodes\NodeImage::createFromArray()
     */
    #[Test]
    public function testCreateFromArray(): void
    {
        $image = NodeImage::createFromArray(['path' => '11.jpg', 'title' => 'Cover art']);

        $this->assertSame('11.jpg', $image->path);
        $this->assertSame('Cover art', $image->title);
    }
}
