<?php
declare(strict_types=1);

namespace Test\Story\Nodes;

use App\Story\Nodes\CombatNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * CombatNodeTest.
 */
#[CoversClass(CombatNode::class)]
class CombatNodeTest extends TestCase
{
    private function sample(): CombatNode
    {
        return new CombatNode(
            id: 30,
            gameId: 'test-game',
            content: 'Some content.',
            enemyName: 'Orco',
            enemyMaxLifePoints: 15,
            enemyStrength: 8,
            enemyAgility: 4,
            targetVictory: 31,
            targetDefeat: 32,
        );
    }

    /**
     * @link \App\Story\Nodes\CombatNode::createFromArray()
     */
    #[Test]
    public function testCreateFromArray(): void
    {
        $node = CombatNode::createFromArray(id: 30, gameId: 'test-game', data: [
            'content' => 'Some content.',
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

        $this->assertSame(30, $node->id);
        $this->assertSame('Orco', $node->enemyName);
        $this->assertSame(15, $node->enemyMaxLifePoints);
        $this->assertSame(8, $node->enemyStrength);
        $this->assertSame(4, $node->enemyAgility);
        $this->assertSame(31, $node->targetVictory);
        $this->assertSame(32, $node->targetDefeat);
    }

    /**
     * `type` is a literal `'combat'` in `toArray()`, not derived from anything — matches the same convention every
     * other node type now follows (see `DiceNodeTest::testToArray()` and siblings).
     *
     * @link \App\Story\Nodes\CombatNode::toArray()
     */
    #[Test]
    public function testToArray(): void
    {
        $node = $this->sample();

        $this->assertSame([
            'content' => 'Some content.',
            'type' => 'combat',
            'combat' => [
                'enemy_name' => 'Orco',
                'enemy_max_life_points' => 15,
                'enemy_strength' => 8,
                'enemy_agility' => 4,
                'target_victory' => 31,
                'target_defeat' => 32,
            ],
        ], $node->toArray());
    }
}
