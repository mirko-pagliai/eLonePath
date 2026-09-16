<?php
declare(strict_types=1);

namespace Test\TestCase\Story\Nodes;

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
            gameId: 'valid-game',
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
        $node = CombatNode::createFromArray(id: 30, gameId: 'valid-game', data: [
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
     * With no `frighteningThreshold` given, it defaults to `null` — an enemy the story doesn't declare one for
     * never grants a free strike, regardless of the character's own `willpower`.
     *
     * @link \App\Story\Nodes\CombatNode::__construct()
     */
    #[Test]
    public function testFrighteningThresholdDefaultsToNull(): void
    {
        $this->assertNull($this->sample()->frighteningThreshold);
    }

    /**
     * @link \App\Story\Nodes\CombatNode::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayWithFrighteningThreshold(): void
    {
        $node = CombatNode::createFromArray(id: 30, gameId: 'valid-game', data: [
            'content' => 'Some content.',
            'type' => 'combat',
            'combat' => [
                'enemy_name' => 'Orco',
                'enemy_max_life_points' => 15,
                'enemy_strength' => 8,
                'enemy_agility' => 4,
                'target_victory' => 31,
                'target_defeat' => 32,
                'frightening_threshold' => 4,
            ],
        ]);

        $this->assertSame(4, $node->frighteningThreshold);
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

    /**
     * `frightening_threshold` only appears in the array when the node actually has one — matching how
     * `createFromArray()` treats it as optional on the way in.
     *
     * @link \App\Story\Nodes\CombatNode::toArray()
     */
    #[Test]
    public function testToArrayWithFrighteningThreshold(): void
    {
        $node = new CombatNode(
            id: 30,
            gameId: 'valid-game',
            content: 'Some content.',
            enemyName: 'Orco',
            enemyMaxLifePoints: 15,
            enemyStrength: 8,
            enemyAgility: 4,
            targetVictory: 31,
            targetDefeat: 32,
            frighteningThreshold: 4,
        );

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
                'frightening_threshold' => 4,
            ],
        ], $node->toArray());
    }
}
