<?php
declare(strict_types=1);

namespace App\Story;

use App\Story\Combat\Combatant;
use Elone\Core\Contract\Arrayable;
use RuntimeException;
use TypeError;

/**
 * Represents a character in a story.
 *
 * - Strength primarily represents physical ability and plays a direct role in combat, particularly in the
 * effectiveness/damage of attacks.
 * In combat, strength determines how effectively you damage your opponent.
 * - Agility represents speed, coordination, and the ability to land attacks and avoid those of your opponent.
 * In combat, agility determines how effectively you can strike and avoid blows.
 * - Perception is strongly linked to the narrative/exploratory part, without necessarily being excluded from combat.
 * Perception can allow you to spot an enemy before they attack.
 * It can also be used in narrative paragraphs to identify hidden elements, notice details, recognize situations, etc.
 * - Willpower is closely tied to narrative.
 * One possible use is to manage fear or intimidation.
 * Against a particularly fearsome foe, for example, a sufficiently high Willpower might allow the character to
 * maintain control and gain a temporary advantage in combat, such as a few additional points.
 * It can also be used in narrative paragraphs to resist fear, pressure, pain, intimidation, or other similar
 * situations.
 *
 * Strength and Agility each range 4-10; Perception and Willpower each range 1-5 — all four must sum to exactly
 * `TOTAL_ATTRIBUTE_POINTS`. The narrower range on Perception/Willpower, and the floor (not just a ceiling) on
 * Strength/Agility, both exist for the same reason: without them, `Combat`'s formula lets a character dump
 * everything into Agility and become nearly unbeatable, since Agility alone decides who lands every hit. Capping
 * how far any single attribute can be pushed is what keeps the four a real trade-off instead of one dominant
 * choice.
 *
 * `lifePoints` and `maxLifePoints` are deliberately two separate fields, not one: `maxLifePoints` is fixed for the
 * character's whole story (set once, here, and never changed by `withDamage()`/`withHeal()`), while `lifePoints` is
 * the current value, moving within `0..maxLifePoints` as the story plays out. `Character` stays fully immutable —
 * `withDamage()` and `withHeal()` each return a new instance rather than changing this one, the same way every
 * other domain object in this codebase (`Game`, `Node` and its subclasses, `Choice`) represents a change of state
 * as a new value rather than a mutation.
 *
 * The constructor itself allows `lifePoints` and `maxLifePoints` to differ (needed to reconstruct a character
 * mid-story, already damaged, from previously-saved state); `createNew()` is the dedicated entry point for the
 * one case where they must start equal — a brand-new character, at the start of a story, at full health.
 *
 * `final`, matching every other immutable domain object in `App\Story\Combat` (`Enemy`, `Combatant`,
 * `CombatRoundResult`) that returns `new static(...)` from a wither method — without it, PHPStan can't prove a
 * subclass's constructor still accepts the same arguments, since nothing stops one from overriding it
 * incompatibly.
 *
 * @phpstan-type CharacterData array{
 *     max_life_points: int,
 *     life_points: int,
 *     strength: int,
 *     agility: int,
 *     perception: int,
 *     willpower: int,
 * }
 */
final class Character implements Arrayable
{
    /**
     * The default `maxLifePoints` for a newly created character, used whenever a story doesn't specify its own —
     * lives here, not on `StoryController`, since it's a fact about characters, not about handling a web request.
     * Currently the only value every story gets; the name says "default" rather than just "starting" so that a
     * later per-story override (read from that story's own `story.json`, once that's built) has an obvious place
     * to fall back to, without this constant needing to move or be renamed when that happens.
     */
    public const int DEFAULT_MAX_LIFE_POINTS = 20;

    /**
     * The total of a character's four attributes must sum to exactly.
     */
    private const int TOTAL_ATTRIBUTE_POINTS = 20;

    /**
     * @throws \RuntimeException If `$maxLifePoints` is less than 1, if `$lifePoints` is outside `0..maxLifePoints`,
     * if `$strength` or `$agility` is outside 4-10, if `$perception` or `$willpower` is outside 1-5, or if the four
     * attributes don't sum to `TOTAL_ATTRIBUTE_POINTS`.
     */
    public function __construct(
        protected(set) readonly int $maxLifePoints,
        protected(set) readonly int $lifePoints,
        protected(set) readonly int $strength,
        protected(set) readonly int $agility,
        protected(set) readonly int $perception,
        protected(set) readonly int $willpower,
    ) {
        if ($this->maxLifePoints < 1) {
            throw new RuntimeException("The maxLifePoints attribute must be at least 1, got `$this->maxLifePoints`.");
        }

        if ($this->lifePoints < 0 || $this->lifePoints > $this->maxLifePoints) {
            throw new RuntimeException(
                "The lifePoints attribute must be between 0 and maxLifePoints ($this->maxLifePoints), " .
                "got `$this->lifePoints`.",
            );
        }

        if ($this->strength < 4 || $this->strength > 10) {
            throw new RuntimeException("The strength attribute must be between 4 and 10, got `$this->strength`.");
        }

        if ($this->agility < 4 || $this->agility > 10) {
            throw new RuntimeException("The agility attribute must be between 4 and 10, got `$this->agility`.");
        }

        if ($this->perception < 1 || $this->perception > 5) {
            throw new RuntimeException("The perception attribute must be between 1 and 5, got `$this->perception`.");
        }

        if ($this->willpower < 1 || $this->willpower > 5) {
            throw new RuntimeException("The willpower attribute must be between 1 and 5, got `$this->willpower`.");
        }

        $sum = $this->strength + $this->agility + $this->perception + $this->willpower;
        if ($sum !== self::TOTAL_ATTRIBUTE_POINTS) {
            throw new RuntimeException(__(
                "The sum of the character's attributes must be {0}, got {1}.",
                self::TOTAL_ATTRIBUTE_POINTS,
                $sum,
            ));
        }
    }

    /**
     * Builds a brand-new character, at the start of a story: `lifePoints` starts equal to `$maxLifePoints`, by
     * construction, rather than left to the caller to set correctly by hand every time. See the class docblock for
     * why the regular constructor can't just default `lifePoints` to `$maxLifePoints` itself — it's also the one
     * used to reconstruct an existing, already-damaged character, where the two legitimately differ.
     *
     * @throws \RuntimeException Same conditions as `__construct()`.
     */
    public static function createNew(
        int $maxLifePoints,
        int $strength,
        int $agility,
        int $perception,
        int $willpower,
    ): static {
        return new static(
            maxLifePoints: $maxLifePoints,
            lifePoints: $maxLifePoints,
            strength: $strength,
            agility: $agility,
            perception: $perception,
            willpower: $willpower,
        );
    }

    /**
     * Builds a brand-new character with a random, valid distribution of the 20-point attribute budget — for
     * anyone who'd rather not work out a distribution by hand. Perception and Willpower are each rolled within
     * their own 1-5 range first; whatever's left of the budget is then split between Strength and Agility, each
     * kept within its own 4-10 range. Delegates to `createNew()` for the actual construction, so a random
     * character is subject to exactly the same rules as a hand-picked one, not a separate path that could drift
     * from them.
     *
     * @throws \Random\RandomException
     */
    public static function createRandom(int $maxLifePoints): static
    {
        $perception = random_int(1, 5);
        $willpower = random_int(1, 5);

        $remaining = self::TOTAL_ATTRIBUTE_POINTS - $perception - $willpower;
        // Keeps both strength and agility within 4-10: strength can't be so low that the rest would push agility
        // over 10, nor so high that the rest would leave agility under 4.
        $minStrength = max(4, $remaining - 10);
        $maxStrength = min(10, $remaining - 4);
        // Both bounds come from the same $remaining, which keeps $minStrength <= $maxStrength for every
        // possible value of $remaining (10-18) — verified exhaustively; PHPStan tracks the two ranges
        // independently and can't see that correlation.
        $strength = random_int($minStrength, $maxStrength); // @phpstan-ignore argument.type
        $agility = $remaining - $strength;

        return self::createNew(
            maxLifePoints: $maxLifePoints,
            strength: $strength,
            agility: $agility,
            perception: $perception,
            willpower: $willpower,
        );
    }

    /**
     * Whether this character has been defeated — `lifePoints` reached `0`.
     */
    public function isDefeated(): bool
    {
        return $this->lifePoints === 0;
    }

    /**
     * A new `Character`, identical to this one except `lifePoints` reduced by `$amount`, floored at `0` — it can
     * never go negative, and `isDefeated()` is how a caller finds out combat is over.
     *
     * @throws \RuntimeException If `$amount` is negative.
     */
    public function withDamage(int $amount): static
    {
        if ($amount < 0) {
            throw new RuntimeException("Damage amount must not be negative, got `$amount`.");
        }

        return new static(
            maxLifePoints: $this->maxLifePoints,
            lifePoints: max(0, $this->lifePoints - $amount),
            strength: $this->strength,
            agility: $this->agility,
            perception: $this->perception,
            willpower: $this->willpower,
        );
    }

    /**
     * A new `Character`, identical to this one except `lifePoints` increased by `$amount`, capped at
     * `maxLifePoints` — healing can restore lost life points, but never push them past the character's own
     * starting maximum.
     *
     * @throws \RuntimeException If `$amount` is negative.
     */
    public function withHeal(int $amount): static
    {
        if ($amount < 0) {
            throw new RuntimeException("Heal amount must not be negative, got `$amount`.");
        }

        return new static(
            maxLifePoints: $this->maxLifePoints,
            lifePoints: min($this->maxLifePoints, $this->lifePoints + $amount),
            strength: $this->strength,
            agility: $this->agility,
            perception: $this->perception,
            willpower: $this->willpower,
        );
    }

    /**
     * The stats this character brings into a single combat round — see `App\Story\Combat\Combat::resolveRound()`.
     * A plain snapshot, deliberately not `$this` itself: `Combat` only ever needs to know three numbers, not the
     * whole `Character` (narrative attributes, validation rules, and — once written — an inventory none of that
     * concerns combat math directly). Once equipped weapons exist, this is the one place their strength/agility
     * bonuses get folded in, without `Combat` itself ever needing to change.
     */
    public function toCombatant(): Combatant
    {
        return new Combatant(strength: $this->strength, agility: $this->agility, lifePoints: $this->lifePoints);
    }

    /**
     * Exports this character as a plain array — used by `GameState` to carry it through the URL between pages,
     * since there's no server-side session. The reverse of `createFromArray()`.
     *
     * @return CharacterData
     */
    public function toArray(): array
    {
        return [
            'max_life_points' => $this->maxLifePoints,
            'life_points' => $this->lifePoints,
            'strength' => $this->strength,
            'agility' => $this->agility,
            'perception' => $this->perception,
            'willpower' => $this->willpower,
        ];
    }

    /**
     * @param CharacterData $data
     * @throws \RuntimeException Same conditions as `__construct()`.
     * @throws \TypeError If `$data` is missing a required key, or holds the wrong type for it. Whether this is
     *  worth catching, and turning into something clearer, is a decision for the caller —
     *  `GameState::fromQueryValue()` does exactly that, since a `?state=` value is untrusted input a curious
     *  player could hand-edit; a `story.json` node's data, by contrast, is trusted, author-written content, the
     *  same as every other `createFromArray()` in this codebase that doesn't guard against a malformed shape
     *  either.
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            maxLifePoints: self::requiredIntKey($data, 'max_life_points'),
            lifePoints: self::requiredIntKey($data, 'life_points'),
            strength: self::requiredIntKey($data, 'strength'),
            agility: self::requiredIntKey($data, 'agility'),
            perception: self::requiredIntKey($data, 'perception'),
            willpower: self::requiredIntKey($data, 'willpower'),
        );
    }

    /**
     * Reads `$data[$key]`, requiring it to be an `int` — a missing key, or one holding the wrong type, throws
     * the same `TypeError` that passing it straight through to the constructor's typed (non-nullable) parameter
     * eventually would, just without also emitting PHP's own "Undefined array key" warning along the way, and
     * without asking PHPStan to trust the whole `CharacterData` shape at this narrower point. `$data` is
     * deliberately typed as a plain array, not `CharacterData` — from `createFromArray()`'s own perspective
     * every key is always present and already an `int`, so PHPStan would (rightly, for that narrower view) call
     * checking it redundant; this method exists to step outside that guarantee for the one caller —
     * `GameState::fromQueryValue()` — that can't make it, since a `?state=` value is untrusted, hand-editable
     * input.
     *
     * @param array<array-key, mixed> $data
     * @throws \TypeError If `$data[$key]` is absent, or isn't an `int`.
     */
    private static function requiredIntKey(array $data, string $key): int
    {
        $value = $data[$key] ?? null;

        if (!is_int($value)) {
            throw new TypeError(
                "Character data key `$key` must be an int, " . get_debug_type($value) . ' given.',
            );
        }

        return $value;
    }
}
