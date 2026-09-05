<?php
declare(strict_types=1);

namespace App\Story;

use JsonException;
use RuntimeException;
use TypeError;

/**
 * The player's state carried between pages via the URL's `?state=` query parameter — there is no server-side
 * session, so this is the only way a `Character` (and, mid-fight, the enemy's remaining life points) survives from
 * one page load to the next. `toQueryValue()`/`fromQueryValue()` are the two ends of that round trip:
 * base64-encoded JSON, chosen so the whole thing is one opaque, URL-safe token — `Route::resolve()`'s own
 * `http_build_query()` still percent-encodes it like any other querystring value, but nothing inside it (a `/`
 * from a JSON structure, for instance) needs escaping of its own first.
 *
 * `fromQueryValue()` treats its input as untrusted — unlike `Character::createFromArray()`'s own callers
 * elsewhere (`story.json` is static, author-written content), a `?state=` value is sitting in the URL, editable
 * by hand by anyone curious enough to try. That's why this class, and not `Character`, is where a malformed
 * shape gets turned into a clean `RuntimeException` instead of a raw `TypeError` escaping to `ErrorHandler` as a
 * generic 500.
 *
 * @phpstan-import-type CharacterData from \App\Story\Character
 * @phpstan-type GameStateData array{player: CharacterData, enemyLifePoints?: int}
 */
final readonly class GameState
{
    public function __construct(
        public Character $player,
        public ?int $enemyLifePoints = null,
    ) {
    }

    /**
     * Encodes this state into the string that goes in the URL as `?state=...`.
     *
     * @throws \RuntimeException If the state somehow can't be encoded as JSON — not expected in practice, since
     *  `Character::toArray()` only ever produces plain scalars.
     */
    public function toQueryValue(): string
    {
        /** @var GameStateData $data */
        $data = ['player' => $this->player->toArray()];

        if ($this->enemyLifePoints !== null) {
            $data['enemyLifePoints'] = $this->enemyLifePoints;
        }

        try {
            $json = json_encode($data, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Failed to encode game state.', previous: $exception);
        }

        return base64_encode($json);
    }

    /**
     * Decodes `$value` (as produced by `toQueryValue()`) back into a `GameState`.
     *
     * @param string|null $value The raw `?state=` query value, or `null` if it wasn't present at all.
     * @throws \RuntimeException If `$value` is `null`, isn't valid base64, doesn't decode to valid JSON, or the
     *  decoded `player` data is missing or doesn't match what `Character::createFromArray()` expects —
     *  including a value that satisfies `Character`'s own validation rules (e.g. an attribute sum that isn't
     *  20), whose message is `Character`'s own, re-thrown as-is rather than wrapped.
     */
    public static function fromQueryValue(?string $value): self
    {
        if ($value === null) {
            throw new RuntimeException('Missing game state.');
        }

        $decoded = base64_decode($value, strict: true);
        if ($decoded === false) {
            throw new RuntimeException('Invalid game state: not valid base64.');
        }

        try {
            $data = json_decode($decoded, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Invalid game state: not valid JSON.', previous: $exception);
        }

        if (!is_array($data) || !isset($data['player']) || !is_array($data['player'])) {
            throw new RuntimeException('Invalid game state: missing player data.');
        }

        $enemyLifePoints = $data['enemyLifePoints'] ?? null;

        /** @var CharacterData $playerData */
        $playerData = $data['player'];

        try {
            $player = Character::createFromArray($playerData);
        } catch (TypeError $error) {
            // A key is missing, or holds the wrong type entirely (a string where an int belongs, say) — this is
            // what turns that into the same clean exception every other malformed-state case here already
            // throws, instead of a raw TypeError reaching `ErrorHandler` as an undifferentiated 500.
            throw new RuntimeException('Invalid game state: malformed player data.', previous: $error);
        }

        return new self(
            player: $player,
            enemyLifePoints: is_int($enemyLifePoints) ? $enemyLifePoints : null,
        );
    }

    /**
     * A new `GameState`, identical to this one except `enemyLifePoints` set to `$value` — used to update the
     * enemy's remaining life points as a fight progresses, one round at a time.
     */
    public function withEnemyLifePoints(?int $value): self
    {
        return new self(player: $this->player, enemyLifePoints: $value);
    }
}
