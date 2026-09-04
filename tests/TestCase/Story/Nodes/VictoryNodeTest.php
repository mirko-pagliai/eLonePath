<?php
declare(strict_types=1);

namespace Test\Story\Nodes;

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
     * @link \App\Story\Nodes\VictoryNode::createFromArray()
     */
    #[Test]
    public function testCreateFromArray(): void
    {
        $node = VictoryNode::createFromArray(id: 15, gameId: 'test-game', data: [
            'content' => 'The end.',
            'type' => 'victory',
        ]);

        $this->assertSame(15, $node->id);
        $this->assertSame('The end.', $node->content);
    }

    /**
     * @link \App\Story\Nodes\VictoryNode::toArray()
     */
    #[Test]
    public function testToArray(): void
    {
        $node = new VictoryNode(id: 15, gameId: 'test-game', content: 'The end.');

        $this->assertSame([
            'content' => 'The end.',
            'type' => 'victory',
        ], $node->toArray());
    }
}
