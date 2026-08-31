<?php
declare(strict_types=1);

namespace Test\Story;

use App\Story\DefeatNode;
use App\Story\NodeType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * DefeatNodeTest.
 */
#[CoversClass(DefeatNode::class)]
class DefeatNodeTest extends TestCase
{
    /**
     * @link \App\Story\DefeatNode::type()
     */
    #[Test]
    public function testType(): void
    {
        $node = new DefeatNode(id: 12, gameId: 'test-game', content: 'The end.', image: null);
        $this->assertSame(NodeType::DEFEAT, $node->type());
    }

    /**
     * @link \App\Story\DefeatNode::createFromArray()
     */
    #[Test]
    public function testCreateFromArray(): void
    {
        $node = DefeatNode::createFromArray(id: 12, gameId: 'test-game', data: [
            'content' => 'The end.',
            'image' => null,
            'type' => 'defeat',
        ]);

        $this->assertSame(12, $node->id);
        $this->assertNull($node->image);
    }
}
