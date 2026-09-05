<?php
declare(strict_types=1);

namespace Test\Story\Nodes;

use App\Story\Nodes\CombatNode;
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
     * @link \App\Story\Nodes\NodeFactory::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayDispatchesToCombatNode(): void
    {
        $node = NodeFactory::createFromArray(id: 30, gameId: 'test-game', data: [
            'content' => 'A fight.',
            'type' => 'combat',
            'combat' => [
                'enemy_name' => 'Orco',
                'enemy_max_life_points' => 15,
                'enemy_strength' => 8,
                'enemy_agility' => 4,
                'target_victory' => 31,
                'target_defeat' => 32,
            ],
        ]);

        $this->assertInstanceOf(CombatNode::class, $node);
    }

    /**
     * The `match` in `createFromArray()` falls through to an explicit `RuntimeException` for anything that
     * isn't one of the known type strings.
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
