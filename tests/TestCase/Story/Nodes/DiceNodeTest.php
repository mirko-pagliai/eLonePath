<?php
declare(strict_types=1);

namespace Test\TestCase\Story\Nodes;

use App\Story\Nodes\DiceNode;
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
            gameId: 'valid-game',
            content: 'Some content.',
            requiredRolls: 2,
            minimum: 8,
            targetSuccess: 15,
            targetFailure: 18,
        );
    }

    /**
     * @link \App\Story\Nodes\DiceNode::isSuccess()
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
     * @link \App\Story\Nodes\DiceNode::targetFor()
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
     * With no `perceptionBypass` given, it defaults to `null` — a node that doesn't declare one is never
     * bypassable, regardless of the character's own `perception`.
     *
     * @link \App\Story\Nodes\DiceNode::__construct()
     */
    #[Test]
    public function testPerceptionBypassDefaultsToNull(): void
    {
        $this->assertNull($this->sample()->perceptionBypass);
    }

    /**
     * @link \App\Story\Nodes\DiceNode::createFromArray()
     */
    #[Test]
    public function testCreateFromArray(): void
    {
        $node = DiceNode::createFromArray(id: 17, gameId: 'valid-game', data: [
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
        $this->assertNull($node->perceptionBypass);
    }

    /**
     * @link \App\Story\Nodes\DiceNode::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayWithPerceptionBypass(): void
    {
        $node = DiceNode::createFromArray(id: 17, gameId: 'valid-game', data: [
            'content' => 'Some content.',
            'type' => 'dice',
            'dice' => [
                'required_rolls' => 1,
                'minimum' => 4,
                'target_success' => 15,
                'target_failure' => 18,
                'perception_bypass' => 4,
            ],
        ]);

        $this->assertSame(4, $node->perceptionBypass);
    }

    /**
     * @link \App\Story\Nodes\DiceNode::toArray()
     */
    #[Test]
    public function testToArray(): void
    {
        $node = $this->sample();

        $this->assertSame([
            'content' => 'Some content.',
            'type' => 'dice',
            'dice' => [
                'required_rolls' => 2,
                'minimum' => 8,
                'target_success' => 15,
                'target_failure' => 18,
            ],
        ], $node->toArray());
    }

    /**
     * `perception_bypass` only appears in the array when the node actually has one — matching how
     * `createFromArray()` treats it as optional on the way in.
     *
     * @link \App\Story\Nodes\DiceNode::toArray()
     */
    #[Test]
    public function testToArrayWithPerceptionBypass(): void
    {
        $node = new DiceNode(
            id: 17,
            gameId: 'valid-game',
            content: 'Some content.',
            requiredRolls: 1,
            minimum: 4,
            targetSuccess: 15,
            targetFailure: 18,
            perceptionBypass: 4,
        );

        $this->assertSame([
            'content' => 'Some content.',
            'type' => 'dice',
            'dice' => [
                'required_rolls' => 1,
                'minimum' => 4,
                'target_success' => 15,
                'target_failure' => 18,
                'perception_bypass' => 4,
            ],
        ], $node->toArray());
    }
}
