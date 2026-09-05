<?php
declare(strict_types=1);

namespace App\Story;

use JsonException;
use RuntimeException;

/**
 * The player's state carried between pages via the URL's `?state=` query parameter — there is no server-side
 * session, so this is the only way a `Character` (and, mid-fight, the enemy's remaining life points) survives from
 * one page load to the next. `toQueryValue()`/`fromQueryValue()` are the two ends of that round trip:
 * base64-encoded JSON, chosen so the whole thing is one opaque, URL-safe token — `Route::resolve()`'s own
 * `http_build_query()` still percent-encodes it like any other querystring value, but nothing inside it (a `/`
 * from a JSON structure, for instance) needs escaping of its own first.
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
     * @throws \RuntimeException If `$value` is `null`, isn't valid base64, doesn't decode to valid JSON, or
     *  doesn't match the expected shape — including whatever `Character::createFromArray()` itself would reject.
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

        return new self(
            player: Character::createFromArray($playerData),
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
