<?php
declare(strict_types=1);

namespace Test\Story\Nodes;

use App\Story\Nodes\NodeImage;
use App\Story\Nodes\NodeType;
use App\Story\Nodes\VictoryNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * VictoryNodeTest.
 */
#[CoversClass(VictoryNode::class)]
class VictoryNodeTest extends TestCase
{
    /**
     * @link \App\Story\Nodes\VictoryNode::type()
     */
    #[Test]
    public function testType(): void
    {
        $node = new VictoryNode(id: 15, gameId: 'test-game', content: 'The end.', image: null);
        $this->assertSame(NodeType::VICTORY, $node->type());
    }

    /**
     * @link \App\Story\Nodes\VictoryNode::createFromArray()
     */
    #[Test]
    public function testCreateFromArray(): void
    {
        $node = VictoryNode::createFromArray(id: 15, gameId: 'test-game', data: [
            'content' => 'The end.',
            'image' => ['path' => 'win.jpg', 'title' => 'Victory'],
            'type' => 'victory',
        ]);

        $this->assertSame(15, $node->id);
        $this->assertInstanceOf(NodeImage::class, $node->image);
        $this->assertSame('win.jpg', $node->image->path);
        $this->assertSame('Victory', $node->image->title);
    }
}
