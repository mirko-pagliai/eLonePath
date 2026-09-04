<?php
declare(strict_types=1);

namespace App\Story;

use App\Story\Combat\Combatant;
use RuntimeException;

/**
 * An enemy the player can fight — the opposing side of a `Combat::resolveRound()` exchange.
 *
 * Deliberately much simpler than `Character`: no Perception or Willpower (those describe how the *player* reacts
 * to an enemy — spotting it early, resisting its fear — not something the enemy itself needs), and no fixed point
 * budget to validate against. A story author picks whatever `strength`/`agility`/`lifePoints` suit one specific
 * encounter — as weak or as fearsome as the story calls for — rather than building an `Enemy` under the same
 * 20-point constraint a player character is built under.
 *
 * Immutable, the same way `Character` is: `withDamage()` returns a new instance rather than changing this one.
 */
final readonly class Enemy
{
    /**
     * @throws \RuntimeException If `$maxLifePoints` is less than 1, if `$lifePoints` is outside
     * `0..maxLifePoints`, or if `$strength` or `$agility` is less than 1.
     */
    public function __construct(
        public string $name,
        public int $maxLifePoints,
        public int $lifePoints,
        public int $strength,
        public int $agility,
    ) {
        if ($this->maxLifePoints < 1) {
            throw new RuntimeException(
                "The maxLifePoints attribute must be at least 1, got `$this->maxLifePoints`.",
            );
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
    }

    /**
     * Builds a fresh `Enemy` at full health — the usual way one is defined for a specific encounter. See
     * `Character::createNew()` for why this is a separate method rather than a default on the constructor.
     *
     * @throws \RuntimeException Same conditions as `__construct()`.
     */
    public static function createNew(string $name, int $maxLifePoints, int $strength, int $agility): static
    {
        return new static(
            name: $name,
            maxLifePoints: $maxLifePoints,
            lifePoints: $maxLifePoints,
            strength: $strength,
            agility: $agility,
        );
    }

    /**
     * Whether this enemy has been defeated — `lifePoints` reached `0`.
     */
    public function isDefeated(): bool
    {
        return $this->lifePoints === 0;
    }

    /**
     * A new `Enemy`, identical to this one except `lifePoints` reduced by `$amount`, floored at `0`.
     *
     * @throws \RuntimeException If `$amount` is negative.
     */
    public function withDamage(int $amount): static
    {
        if ($amount < 0) {
            throw new RuntimeException("Damage amount must not be negative, got `$amount`.");
        }

        return new static(
            name: $this->name,
            maxLifePoints: $this->maxLifePoints,
            lifePoints: max(0, $this->lifePoints - $amount),
            strength: $this->strength,
            agility: $this->agility,
        );
    }

    /**
     * This enemy's stats for a single combat round — see `App\Story\Combat\Combat::resolveRound()`.
     */
    public function toCombatant(): Combatant
    {
        return new Combatant(strength: $this->strength, agility: $this->agility, lifePoints: $this->lifePoints);
    }
}
