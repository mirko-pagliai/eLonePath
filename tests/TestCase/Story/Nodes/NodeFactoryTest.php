<?php
declare(strict_types=1);

namespace Test\Story\Nodes;

use App\Story\Nodes\DefeatNode;
use App\Story\Nodes\DiceNode;
use App\Story\Nodes\NodeFactory;
use App\Story\Nodes\PassageNode;
use App\Story\Nodes\VictoryNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * NodeFactoryTest.
 */
#[CoversClass(NodeFactory::class)]
class NodeFactoryTest extends TestCase
{
    /**
     * @link \App\Story\Nodes\NodeFactory::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayDispatchesToPassageNode(): void
    {
        $node = NodeFactory::createFromArray(id: 1, gameId: 'test-game', data: [
            'content' => 'Some content.',
            'type' => 'passage',
            'choices' => [],
        ]);

        $this->assertInstanceOf(PassageNode::class, $node);
    }

    /**
     * @link \App\Story\Nodes\NodeFactory::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayDispatchesToDiceNode(): void
    {
        $node = NodeFactory::createFromArray(id: 17, gameId: 'test-game', data: [
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
     * @link \App\Story\Nodes\NodeFactory::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayDispatchesToVictoryNode(): void
    {
        $node = NodeFactory::createFromArray(id: 15, gameId: 'test-game', data: [
            'content' => 'The end.',
            'type' => 'victory',
        ]);

        $this->assertInstanceOf(VictoryNode::class, $node);
    }

    /**
     * @link \App\Story\Nodes\NodeFactory::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayDispatchesToDefeatNode(): void
    {
        $node = NodeFactory::createFromArray(id: 12, gameId: 'test-game', data: [
            'content' => 'The end.',
            'type' => 'defeat',
        ]);

        $this->assertInstanceOf(DefeatNode::class, $node);
    }

    /**
     * The `match` in `createFromArray()` falls through to an explicit `RuntimeException` for anything that
     * isn't one of the four known type strings — previously, an unrecognized type reached `NodeType::from()`
     * instead, which threw its own `ValueError` automatically; nothing tested that path either, but it's worth
     * covering now that the check (and its message) is this class's own code, not something inherited for free.
     *
     * @link \App\Story\Nodes\NodeFactory::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayWithUnknownTypeThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Unknown node type: `mystery`.');
        NodeFactory::createFromArray(id: 1, gameId: 'test-game', data: ['content' => 'c', 'type' => 'mystery']);
    }
}
