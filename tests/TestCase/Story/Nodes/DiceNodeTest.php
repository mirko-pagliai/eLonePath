<?php
declare(strict_types=1);

namespace TestCase\Story\Nodes;

use App\Story\Nodes\DiceNode;
use App\Story\Nodes\NodeType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * DiceNodeTest.
 */
#[CoversClass(DiceNode::class)]
class DiceNodeTest extends TestCase
{
    private function sample(): DiceNode
    {
        return new DiceNode(
            id: 17,
            gameId: 'test-game',
            content: 'Some content.',
            image: null,
            requiredRolls: 2,
            minimum: 8,
            targetSuccess: 15,
            targetFailure: 18,
        );
    }

    /**
     * @link \App\Story\DiceNode::type()
     */
    #[Test]
    public function testType(): void
    {
        $this->assertSame(NodeType::DICE, $this->sample()->type());
    }

    /**
     * @link \App\Story\DiceNode::isSuccess()
     */
    #[Test]
    #[TestWith([8, true])]
    #[TestWith([12, true])]
    #[TestWith([7, false])]
    #[TestWith([2, false])]
    public function testIsSuccess(int $total, bool $expected): void
    {
        $result = $this->sample()->isSuccess($total);
        $this->assertSame($expected, $result);
    }

    /**
     * @link \App\Story\DiceNode::targetFor()
     */
    #[Test]
    #[TestWith([8, 15])]
    #[TestWith([12, 15])]
    #[TestWith([7, 18])]
    #[TestWith([2, 18])]
    public function testTargetFor(int $total, int $expectedTarget): void
    {
        $result = $this->sample()->targetFor($total);
        $this->assertSame($expectedTarget, $result);
    }

    /**
     * @link \App\Story\DiceNode::createFromArray()
     */
    #[Test]
    public function testCreateFromArray(): void
    {
        $node = DiceNode::createFromArray(id: 17, gameId: 'test-game', data: [
            'content' => 'Some content.',
            'type' => 'dice',
            'dice' => [
                'required_rolls' => 2,
                'minimum' => 8,
                'target_success' => 15,
                'target_failure' => 18,
            ],
        ]);

        $this->assertSame(17, $node->id);
        $this->assertSame(2, $node->requiredRolls);
        $this->assertSame(8, $node->minimum);
        $this->assertSame(15, $node->targetSuccess);
        $this->assertSame(18, $node->targetFailure);
    }
}
