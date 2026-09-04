<?php
declare(strict_types=1);

namespace App\Story\Combat;

use App\Story\Character;
use App\Story\Enemy;

/**
 * The outcome of a whole fight — see `Fight::resolve()`. `rounds` is every round's own `CombatRoundResult`, in
 * order, meant for a template to render as a battle report (the same idea as `templates/Story/roll.php` narrating
 * a `DiceNode`'s single roll, just one entry per round instead of one). `player`/`enemy` are the two sides' final
 * state — check `player->isDefeated()`/`enemy->isDefeated()` to find out who won.
 */
final readonly class FightResult
{
    /**
     * @param list<\App\Story\Combat\CombatRoundResult> $rounds
     */
    public function __construct(
        public array $rounds,
        public Character $player,
        public Enemy $enemy,
    ) {
    }

    /**
     * True only if `Fight::resolve()` hit its round safety cap without either side being defeated — an
     * essentially theoretical case with the stat ranges this game uses today (not observed once in 20,000
     * simulated fights during design), but not impossible once weapons can push damage or life points further.
     * What a story should do about it — treat it as a stalemate, a defeat, something else — is a decision for
     * whatever calls `Fight::resolve()`, not for this class.
     */
    public function isDraw(): bool
    {
        return !$this->player->isDefeated() && !$this->enemy->isDefeated();
    }
}
