<?php
declare(strict_types=1);

namespace TestCase\Story\Nodes;

use App\Story\Nodes\DefeatNode;
use App\Story\Nodes\DiceNode;
use App\Story\Nodes\Node;
use App\Story\Nodes\PassageNode;
use App\Story\Nodes\VictoryNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * NodeTest.
 */
#[CoversClass(Node::class)]
class NodeTest extends TestCase
{
    /**
     * @link \App\Story\Node::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayDispatchesToPassageNode(): void
    {
        $node = Node::createFromArray(id: 1, gameId: 'test-game', data: [
            'content' => 'Some content.',
            'type' => 'passage',
            'choices' => [],
        ]);

        $this->assertInstanceOf(PassageNode::class, $node);
    }

    /**
     * @link \App\Story\Node::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayDispatchesToDiceNode(): void
    {
        $node = Node::createFromArray(id: 17, gameId: 'test-game', data: [
            'content' => 'Some content.',
            'type' => 'dice',
            'dice' => [
                'required_rolls' => 2,
                'minimum' => 8,
                'target_success' => 15,
                'target_failure' => 18,
            ],
        ]);

        $this->assertInstanceOf(DiceNode::class, $node);
    }

    /**
     * @link \App\Story\Node::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayDispatchesToVictoryNode(): void
    {
        $node = Node::createFromArray(id: 15, gameId: 'test-game', data: [
            'content' => 'The end.',
            'type' => 'victory',
        ]);

        $this->assertInstanceOf(VictoryNode::class, $node);
    }

    /**
     * @link \App\Story\Node::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayDispatchesToDefeatNode(): void
    {
        $node = Node::createFromArray(id: 12, gameId: 'test-game', data: [
            'content' => 'The end.',
            'type' => 'defeat',
        ]);

        $this->assertInstanceOf(DefeatNode::class, $node);
    }
}
