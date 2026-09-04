<?php
declare(strict_types=1);

namespace App\Story;

use App\Story\Combat\Combatant;
use RuntimeException;

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
 * Strength and Agility have no upper bound of their own — Perception and Willpower do (1-5 each) — because the four
 * must sum to exactly `TOTAL_ATTRIBUTE_POINTS`. Capping every attribute at 1-5 while also requiring that exact sum
 * leaves exactly one possible character (5, 5, 5, 5); the asymmetry is what keeps the point distribution a real
 * choice, weighted toward the two attributes with the most direct role in combat.
 *
 * `lifePoints` and `maxLifePoints` are deliberately two separate fields, not one: `maxLifePoints` is fixed for the
 * character's whole story (set once, here, and never changed by `withDamage()`/`withHeal()`), while `lifePoints` is
 * the current value, moving within `0..maxLifePoints` as the story plays out. `Character` stays fully immutable —
 * `withDamage()` and `withHeal()` each return a new instance rather than changing this one, the same way every
 * other domain object in this codebase (`Game`, `Node` and its subclasses, `Choice`) represents a change of state
 * as a new value rather than a mutation.
 */
class Character
{
    /**
     * The total a character's four attributes must sum to exactly.
     */
    private const int TOTAL_ATTRIBUTE_POINTS = 20;

    /**
     * @throws \RuntimeException If `$maxLifePoints` is less than 1, if `$lifePoints` is outside `0..maxLifePoints`,
     * if `$strength` or `$agility` is less than 1, if `$perception` or `$willpower` is outside 1-5, or if the four
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

        if ($this->strength < 1) {
            throw new RuntimeException("The strength attribute must be at least 1, got `$this->strength`.");
        }

        if ($this->agility < 1) {
            throw new RuntimeException("The agility attribute must be at least 1, got `$this->agility`.");
        }

        if ($this->perception < 1 || $this->perception > 5) {
            throw new RuntimeException("The perception attribute must be between 1 and 5, got `$this->perception`.");
        }

        if ($this->willpower < 1 || $this->willpower > 5) {
            throw new RuntimeException("The willpower attribute must be between 1 and 5, got `$this->willpower`.");
        }

        $sum = $this->strength + $this->agility + $this->perception + $this->willpower;
        if ($sum !== self::TOTAL_ATTRIBUTE_POINTS) {
            throw new RuntimeException(
                'The sum of the character\'s attributes must be ' . self::TOTAL_ATTRIBUTE_POINTS . ", got `$sum`.",
            );
        }
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
}
