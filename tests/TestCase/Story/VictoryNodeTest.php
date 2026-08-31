<?php
declare(strict_types=1);

namespace Test\Story;

use App\Story\NodeType;
use App\Story\VictoryNode;
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
     * @link \App\Story\VictoryNode::type()
     */
    #[Test]
    public function testType(): void
    {
        $node = new VictoryNode(id: 15, gameId: 'test-game', content: 'The end.', image: null);
        $this->assertSame(NodeType::VICTORY, $node->type());
    }

    /**
     * @link \App\Story\VictoryNode::createFromArray()
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
        $this->assertSame(['path' => 'win.jpg', 'title' => 'Victory'], $node->image);
    }
}
