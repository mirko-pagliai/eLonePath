<?php
declare(strict_types=1);

namespace Test\Story\Nodes;

use App\Story\Nodes\DefeatNode;
use App\Story\Nodes\NodeType;
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
     * @link \App\Story\Nodes\DefeatNode::createFromArray()
     */
    #[Test]
    public function testCreateFromArray(): void
    {
        $node = DefeatNode::createFromArray(id: 12, gameId: 'test-game', data: [
            'content' => 'The end.',
            'type' => 'defeat',
        ]);

        $this->assertSame(12, $node->id);
        $this->assertSame('The end.', $node->content);
    }
}
