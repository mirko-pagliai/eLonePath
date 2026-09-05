<?php
declare(strict_types=1);

namespace Test\Story;

use App\Story\Character;
use App\Story\GameState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * GameStateTest.
 */
#[CoversClass(GameState::class)]
class GameStateTest extends TestCase
{
    private function samplePlayer(): Character
    {
        return Character::createNew(maxLifePoints: 20, strength: 9, agility: 5, perception: 3, willpower: 3);
    }

    /**
     * @link \App\Story\GameState::__construct()
     */
    #[Test]
    public function testConstructWithoutEnemyLifePoints(): void
    {
        $state = new GameState(player: $this->samplePlayer());

        $this->assertSame(9, $state->player->strength);
        $this->assertNull($state->enemyLifePoints);
    }

    /**
     * @link \App\Story\GameState::__construct()
     */
    #[Test]
    public function testConstructWithEnemyLifePoints(): void
    {
        $state = new GameState(player: $this->samplePlayer(), enemyLifePoints: 7);

        $this->assertSame(7, $state->enemyLifePoints);
    }

    /**
     * The round trip a `Character` actually goes through between two pages: encoded into a `?state=` value by
     * one request, decoded back by the next — this is what proves the two ends of `GameState` genuinely agree
     * with each other, not just that each one works in isolation.
     *
     * @link \App\Story\GameState::toQueryValue()
     * @link \App\Story\GameState::fromQueryValue()
     */
    #[Test]
    public function testToQueryValueAndFromQueryValueRoundTrip(): void
    {
        $original = new GameState(player: $this->samplePlayer());

        $queryValue = $original->toQueryValue();
        $decoded = GameState::fromQueryValue($queryValue);

        $this->assertSame($original->player->maxLifePoints, $decoded->player->maxLifePoints);
        $this->assertSame($original->player->lifePoints, $decoded->player->lifePoints);
        $this->assertSame($original->player->strength, $decoded->player->strength);
        $this->assertSame($original->player->agility, $decoded->player->agility);
        $this->assertSame($original->player->perception, $decoded->player->perception);
        $this->assertSame($original->player->willpower, $decoded->player->willpower);
        $this->assertNull($decoded->enemyLifePoints);
    }

    /**
     * Same round trip as above, but with `enemyLifePoints` set — this is what proves that value survives the
     * encode/decode cycle too, not just the player.
     *
     * @link \App\Story\GameState::toQueryValue()
     * @link \App\Story\GameState::fromQueryValue()
     */
    #[Test]
    public function testToQueryValueAndFromQueryValueRoundTripWithEnemyLifePoints(): void
    {
        $original = new GameState(player: $this->samplePlayer(), enemyLifePoints: 4);

        $decoded = GameState::fromQueryValue($original->toQueryValue());

        $this->assertSame(4, $decoded->enemyLifePoints);
    }

    /**
     * `toQueryValue()` is meant for a URL — this locks in that the result only ever contains URL-safe base64
     * characters, so nothing further needs escaping before `http_build_query()` gets it (which still
     * percent-encodes the whole value regardless, but this is what confirms there's nothing hiding inside it
     * that would look broken if it weren't).
     *
     * @link \App\Story\GameState::toQueryValue()
     */
    #[Test]
    public function testToQueryValueIsBase64(): void
    {
        $state = new GameState(player: $this->samplePlayer());

        $queryValue = $state->toQueryValue();

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9+\/]+={0,2}$/', $queryValue);
        $this->assertNotFalse(base64_decode($queryValue, strict: true));
    }

    /**
     * @link \App\Story\GameState::fromQueryValue()
     */
    #[Test]
    public function testFromQueryValueWithNullThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Missing game state.');
        GameState::fromQueryValue(null);
    }

    /**
     * @link \App\Story\GameState::fromQueryValue()
     */
    #[Test]
    public function testFromQueryValueWithInvalidBase64Throws(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Invalid game state: not valid base64.');
        // Contains characters that never appear in base64 output.
        GameState::fromQueryValue('not valid base64!!!');
    }

    /**
     * @link \App\Story\GameState::fromQueryValue()
     */
    #[Test]
    public function testFromQueryValueWithInvalidJsonThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Invalid game state: not valid JSON.');
        // Valid base64, but what it decodes to isn't valid JSON.
        GameState::fromQueryValue(base64_encode('not { json'));
    }

    /**
     * @link \App\Story\GameState::fromQueryValue()
     */
    #[Test]
    public function testFromQueryValueWithMissingPlayerKeyThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Invalid game state: missing player data.');
        GameState::fromQueryValue(base64_encode(json_encode(['enemyLifePoints' => 5])));
    }

    /**
     * A malformed `player` value — valid JSON, valid top-level shape, but `player` isn't itself the object
     * `Character::createFromArray()` expects — is rejected the same way as a missing key entirely, rather than
     * reaching `Character::createFromArray()` with something it can't use.
     *
     * @link \App\Story\GameState::fromQueryValue()
     */
    #[Test]
    public function testFromQueryValueWithNonArrayPlayerThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Invalid game state: missing player data.');
        GameState::fromQueryValue(base64_encode(json_encode(['player' => 'not-an-array'])));
    }

    /**
     * A `player` array that IS an array, but missing the keys `Character::createFromArray()` needs, would
     * otherwise reach `Character`'s constructor with `null` for an `int` parameter and throw a raw `TypeError` —
     * this is what proves `fromQueryValue()` catches that and turns it into the same clean exception every
     * other malformed-state case here throws.
     *
     * @link \App\Story\GameState::fromQueryValue()
     */
    #[Test]
    public function testFromQueryValueWithIncompletePlayerDataThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Invalid game state: malformed player data.');
        GameState::fromQueryValue(base64_encode(json_encode(['player' => ['max_life_points' => 20]])));
    }

    /**
     * Distinct from the case above: every key IS present here, so `Character::createFromArray()` reaches its own
     * constructor successfully — it's `Character`'s own validation (the sum not being 20) that rejects it, and
     * that message is `Character`'s own, not rewritten by `GameState`.
     *
     * @link \App\Story\GameState::fromQueryValue()
     */
    #[Test]
    public function testFromQueryValueWithInvalidCharacterDataThrowsCharactersOwnMessage(): void
    {
        $playerData = [
            'max_life_points' => 20,
            'life_points' => 20,
            'strength' => 1,
            'agility' => 1,
            'perception' => 1,
            'willpower' => 1,
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs("The sum of the character's attributes must be 20, got `4`.");
        GameState::fromQueryValue(base64_encode(json_encode(['player' => $playerData])));
    }

    /**
     * A non-integer `enemyLifePoints` in the decoded JSON (a string, for instance) is treated the same as if it
     * were absent — `null` — rather than propagated as-is or coerced.
     *
     * @link \App\Story\GameState::fromQueryValue()
     */
    #[Test]
    public function testFromQueryValueWithNonIntEnemyLifePointsTreatsItAsNull(): void
    {
        $data = ['player' => $this->samplePlayer()->toArray(), 'enemyLifePoints' => 'not-an-int'];

        $decoded = GameState::fromQueryValue(base64_encode(json_encode($data)));

        $this->assertNull($decoded->enemyLifePoints);
    }

    /**
     * @link \App\Story\GameState::withEnemyLifePoints()
     */
    #[Test]
    public function testWithEnemyLifePointsReturnsNewInstanceUnchangedOriginal(): void
    {
        $original = new GameState(player: $this->samplePlayer(), enemyLifePoints: 10);

        $updated = $original->withEnemyLifePoints(6);

        $this->assertNotSame($original, $updated);
        $this->assertSame(10, $original->enemyLifePoints);
        $this->assertSame(6, $updated->enemyLifePoints);
        $this->assertSame($original->player, $updated->player);
    }

    /**
     * @link \App\Story\GameState::withEnemyLifePoints()
     */
    #[Test]
    public function testWithEnemyLifePointsToNull(): void
    {
        $original = new GameState(player: $this->samplePlayer(), enemyLifePoints: 10);

        $updated = $original->withEnemyLifePoints(null);

        $this->assertNull($updated->enemyLifePoints);
    }
}
