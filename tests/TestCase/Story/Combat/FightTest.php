<?php
declare(strict_types=1);

namespace Test\Story\Combat;

use App\Story\Character;
use App\Story\Combat\CombatHit;
use App\Story\Combat\Fight;
use App\Story\Enemy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * FightTest.
 */
#[CoversClass(Fight::class)]
class FightTest extends TestCase
{
    /**
     * Builds a fixed-sequence roll generator for deterministic tests — no real dice, no faking randomness.
     *
     * @param list<int> $rolls Consumed two at a time: player's roll, then the enemy's, for each round in order.
     * @return callable(): int
     */
    private function fixedRolls(array $rolls): callable
    {
        $index = 0;

        return function () use ($rolls, &$index): int {
            return $rolls[$index++];
        };
    }

    /**
     * @link \App\Story\Combat\Fight::resolve()
     */
    #[Test]
    public function testResolveEndsWhenEnemyIsDefeated(): void
    {
        $player = Character::createNew(maxLifePoints: 20, strength: 10, agility: 6, perception: 2, willpower: 2);
        $enemy = Enemy::createNew(name: 'Topo', maxLifePoints: 5, strength: 1, agility: 1);

        // Il giocatore vince ogni round (roll alto contro roll basso): un solo round basta a superare 5 HP.
        $result = Fight::resolve($player, $enemy, $this->fixedRolls([12, 2]));

        $this->assertCount(1, $result->rounds);
        $this->assertSame(CombatHit::Player, $result->rounds[0]->hit);
        $this->assertTrue($result->enemy->isDefeated());
        $this->assertFalse($result->player->isDefeated());
        $this->assertFalse($result->isDraw());
    }

    /**
     * @link \App\Story\Combat\Fight::resolve()
     */
    #[Test]
    public function testResolveEndsWhenPlayerIsDefeated(): void
    {
        $player = Character::createNew(maxLifePoints: 5, strength: 5, agility: 5, perception: 5, willpower: 5);
        $enemy = Enemy::createNew(name: 'Drago', maxLifePoints: 30, strength: 15, agility: 10);

        $result = Fight::resolve($player, $enemy, $this->fixedRolls([2, 12]));

        $this->assertCount(1, $result->rounds);
        $this->assertSame(CombatHit::Enemy, $result->rounds[0]->hit);
        $this->assertTrue($result->player->isDefeated());
        $this->assertFalse($result->enemy->isDefeated());
    }

    /**
     * A tied round costs nobody life points but still counts as a round — the fight must keep going, not stop
     * early because nothing happened.
     *
     * @link \App\Story\Combat\Fight::resolve()
     */
    #[Test]
    public function testResolveContinuesPastATiedRound(): void
    {
        $player = Character::createNew(maxLifePoints: 10, strength: 10, agility: 6, perception: 2, willpower: 2);
        $enemy = Enemy::createNew(name: 'Guardia', maxLifePoints: 5, strength: 1, agility: 6);

        // Round 1: 7+3 = 10 contro 7+3 = 10 -> pareggio, nessun danno. Round 2: il giocatore vince e chiude.
        $result = Fight::resolve($player, $enemy, $this->fixedRolls([7, 7, 12, 2]));

        $this->assertCount(2, $result->rounds);
        $this->assertSame(CombatHit::None, $result->rounds[0]->hit);
        $this->assertSame(CombatHit::Player, $result->rounds[1]->hit);
        $this->assertTrue($result->enemy->isDefeated());
    }

    /**
     * @link \App\Story\Combat\Fight::resolve()
     */
    #[Test]
    public function testResolveUsesRealDiceByDefault(): void
    {
        $player = Character::createNew(maxLifePoints: 20, strength: 10, agility: 6, perception: 2, willpower: 2);
        $enemy = Enemy::createNew(name: 'Topo', maxLifePoints: 3, strength: 1, agility: 1);

        $result = Fight::resolve($player, $enemy);

        $this->assertTrue($result->enemy->isDefeated() || $result->player->isDefeated() || $result->isDraw());
        $this->assertNotEmpty($result->rounds);
    }

    /**
     * Two sides that always tie (identical Agility, identical rolls every round) never produce a winner — this
     * proves the round cap actually stops the loop instead of spinning forever, rather than leaving that only
     * provable by reading the code.
     *
     * @link \App\Story\Combat\Fight::resolve()
     */
    #[Test]
    public function testResolveStopsAtRoundCapOnAnEndlessStalemate(): void
    {
        $player = Character::createNew(maxLifePoints: 5, strength: 9, agility: 6, perception: 3, willpower: 2);
        $enemy = Enemy::createNew(name: 'Specchio', maxLifePoints: 5, strength: 1, agility: 6);

        $result = Fight::resolve($player, $enemy, $this->fixedRolls(array_fill(0, 200, 7)));

        $this->assertCount(50, $result->rounds);
        $this->assertTrue($result->isDraw());
    }
}
