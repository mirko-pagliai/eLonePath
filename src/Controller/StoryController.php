<?php
declare(strict_types=1);

namespace App\Controller;

use App\Story\Character;
use App\Story\Combat\Combat;
use App\Story\Combat\CombatHit;
use App\Story\Enemy;
use App\Story\Game;
use App\Story\GameState;
use App\Story\Nodes\CombatNode;
use App\Story\Nodes\DiceNode;
use App\Utility\Dice;
use Elone\Core\Server\Response;
use RuntimeException;

/**
 * Manages the gameplay of interactive stories, handling game initialization, progression, and dice-based events.
 */
class StoryController extends AppController
{
    /**
     * @param string $storyId The identifier of the story.
     * @return \App\Story\Game The game instance created from the specified story file.
     */
    protected function getGame(string $storyId): Game
    {
        return Game::createFromFile(STORIES . "/$storyId/story.json");
    }

    /**
     * Reads a POST field as an int, treating anything that isn't numeric — missing, malformed, or a stray array
     * from a malicious submission — as `0` rather than a hard failure: `Character::createNew()`'s own
     * validation already rejects `0` (strength/agility need at least 1) with a clear message, so this doesn't
     * need to duplicate that error handling here. This is what actually narrows `dataParam()`'s `mixed` return
     * to something `(int)` can cast safely — a bare `(int)$this->dataParam(...)` doesn't satisfy PHPStan, since
     * `mixed` includes values (arrays, objects) a plain cast doesn't handle meaningfully.
     *
     * @param string $name The POST field name to read.
     */
    private function intDataParam(string $name): int
    {
        $value = $this->dataParam($name);

        return is_numeric($value) ? (int)$value : 0;
    }

    /**
     * Reads the current `?state=` query parameter, decodes it into the player's `Character`, and makes both the
     * raw value and the character available to the view: the raw value under `state` (so `StoryHelper::link()`
     * can carry it forward into every navigation link on the page) and the character under `character` (so a
     * template can show it — see `templates/element/character_sheet.php`).
     *
     * Call this as the first line of any action that renders or redirects further into the story.
     *
     * @return \App\Story\Character|null The player's character, or `null` if no state is present in the request.
     */
    protected function propagateState(): ?Character
    {
        $stateValue = $this->queryParam('state');
        $stateValue = is_string($stateValue) ? $stateValue : null;

        $character = $stateValue !== null ? GameState::fromQueryValue($stateValue)->player : null;

        $this->set(['state' => $stateValue, 'character' => $character]);

        return $character;
    }

    /**
     * Shows the character creation form (GET) or processes its submission (POST): builds a `Character` from the
     * four submitted attributes, wraps it in a `GameState`, and redirects into `start()` with the resulting
     * `?state=` — the first state a playthrough of this story ever has.
     *
     * @param string $storyId The unique identifier of the story to create a character for.
     * @return \Elone\Core\Server\Response|null Returns a `Response` (a redirect) once a valid character has been
     * submitted; `null` otherwise, to render the form.
     * @link templates/Story/character.php
     */
    public function character(string $storyId): ?Response
    {
        $this->allowMethod(['get', 'post']);

        $game = $this->getGame($storyId);
        $error = null;

        if ($this->is('post')) {
            try {
                $player = Character::createNew(
                    maxLifePoints: Character::DEFAULT_MAX_LIFE_POINTS,
                    strength: $this->intDataParam('strength'),
                    agility: $this->intDataParam('agility'),
                    perception: $this->intDataParam('perception'),
                    willpower: $this->intDataParam('willpower'),
                );

                $state = new GameState(player: $player);

                return $this->redirect(
                    url: ['controller' => 'Story', 'action' => 'start', $storyId],
                    query: ['state' => $state->toQueryValue()],
                );
            } catch (RuntimeException $exception) {
                $error = $exception->getMessage();
            }
        }

        $this->set(compact('game', 'error'));

        return null;
    }

    /**
     * Asks `Character` for a random, valid one — for anyone at the creation form who'd rather not work out a
     * distribution by hand — and redirects into `start()` exactly the way a manual submission would, since the
     * result is a perfectly ordinary `Character`, not a different kind of playthrough. This action only handles
     * the request/response side of that; the random distribution itself is `Character::createRandom()`'s own
     * business; nothing here needs to know how it works.
     *
     * @param string $storyId The unique identifier of the story to create a random character for.
     * @return \Elone\Core\Server\Response A redirect into `start()`, carrying the new character's `?state=`.
     * @throws \Random\RandomException
     * @link templates/Story/character.php
     */
    public function random(string $storyId): Response
    {
        $this->allowMethod('get');

        $player = Character::createRandom(maxLifePoints: Character::DEFAULT_MAX_LIFE_POINTS);

        $state = new GameState(player: $player);

        return $this->redirect(
            url: ['controller' => 'Story', 'action' => 'start', $storyId],
            query: ['state' => $state->toQueryValue()],
        );
    }

    /**
     * If `$game` requires a character and none is present, redirects to `character()` for this story instead of
     * letting the caller continue — closing the gap `?state=` alone leaves open: nothing stops a player from
     * typing a chapter's URL directly, skipping character creation entirely, and `propagateState()` on its own
     * only reads whatever state happens to be there, it doesn't enforce that any should be.
     *
     * Call this right after `propagateState()`, in any action a player could plausibly reach directly by URL.
     */
    private function requireCharacterIfNeeded(Game $game, string $storyId, ?Character $character): ?Response
    {
        if (!$game->requiresCharacter || $character !== null) {
            return null;
        }

        return $this->redirect(url: ['controller' => 'Story', 'action' => 'character', $storyId]);
    }

    /**
     * Starts the game by checking for a preface and determining whether to redirect or set up the necessary game data.
     *
     * @param string $storyId The unique identifier of the story to start.
     * @return \Elone\Core\Server\Response|null Returns a `Response` object if a redirection is performed; otherwise,
     * returns `null`.
     * @link templates/Story/start.php
     */
    public function start(string $storyId): ?Response
    {
        $game = $this->getGame($storyId);

        $character = $this->propagateState();
        if (($response = $this->requireCharacterIfNeeded($game, $storyId, $character)) !== null) {
            return $response;
        }

        $stateValue = $this->queryParam('state');

        // If the game does not have a preface, redirects to the first chapter
        if (!$game->preface) {
            return $this->redirect(
                url: ['controller' => 'Story', 'action' => 'chapter', $storyId, 1],
                query: is_string($stateValue) ? ['state' => $stateValue] : [],
            );
        }

        $this->set(compact('game'));

        return null;
    }

    /**
     * @param string $storyId
     * @param int $nodeNumber
     * @return \Elone\Core\Server\Response|null Returns a redirect to `character()` if this story requires one and
     * none is present; `null` otherwise, to render the chapter.
     * @link templates/Story/chapter.php
     */
    public function chapter(string $storyId, int $nodeNumber): ?Response
    {
        $game = $this->getGame($storyId);
        $node = $game->getNode($nodeNumber);

        $character = $this->propagateState();
        if (($response = $this->requireCharacterIfNeeded($game, $storyId, $character)) !== null) {
            return $response;
        }

        $this->set(compact('game', 'node'));

        return null;
    }

    /**
     * Rolls the dice for a `DiceNode` and shows the outcome, with a link to whichever node it points to.
     *
     * @param string $storyId
     * @param int $nodeNumber
     * @return \Elone\Core\Server\Response|null Returns a redirect to `character()` if this story requires one and
     * none is present; `null` otherwise, to render the roll's outcome.
     * @throws \Random\RandomException
     * @link templates/Story/roll.php
     */
    public function roll(string $storyId, int $nodeNumber): ?Response
    {
        $game = $this->getGame($storyId);
        $node = $game->getNode($nodeNumber);

        $character = $this->propagateState();
        if (($response = $this->requireCharacterIfNeeded($game, $storyId, $character)) !== null) {
            return $response;
        }

        if (!$node instanceof DiceNode) {
            throw new RuntimeException("Node `$nodeNumber` in `$storyId` is not a dice check.");
        }

        $rolls = new Dice()->rollMultiple($node->requiredRolls);
        $total = array_sum($rolls);
        $success = $node->isSuccess($total);
        $target = $node->targetFor($total);

        $this->set(compact('game', 'rolls', 'total', 'success', 'target'));

        return null;
    }

    /**
     * Resolves one round of combat against a `CombatNode`'s enemy, and shows the outcome — the same "GET
     * computes and shows a result" idiom `roll()` uses for a dice check, just repeated round after round instead
     * of resolved in one shot: the enemy's current life points travel in `GameState::$enemyLifePoints`, absent
     * on the first round against this node (the enemy starts at the full health the node itself declares) and
     * present on every round after, updated each time.
     *
     * Ends the fight the moment either side reaches `0` life points, redirecting to whichever of the node's own
     * `targetVictory`/`targetDefeat` applies — carrying the player's own final state forward either way, but
     * dropping `enemyLifePoints`: the fight is over, there's nothing left to track.
     *
     * @param string $storyId
     * @param int $nodeNumber
     * @return \Elone\Core\Server\Response|null Returns a redirect once the fight ends; `null` otherwise, to show
     * this round's outcome with a link to continue the same fight.
     * @throws \RuntimeException If the node isn't a `CombatNode`, or if no character is present in the request —
     * a fight can't be resolved without one.
     * @throws \Random\RandomException
     *
     * @link templates/Story/fight.php
     */
    public function fight(string $storyId, int $nodeNumber): ?Response
    {
        $game = $this->getGame($storyId);
        $node = $game->getNode($nodeNumber);

        $character = $this->propagateState();

        if (!$node instanceof CombatNode) {
            throw new RuntimeException("Node `$nodeNumber` in `$storyId` is not a combat.");
        }

        if ($character === null) {
            throw new RuntimeException("No character found for `$storyId` — create one before fighting.");
        }

        $stateValue = $this->queryParam('state');
        assert(is_string($stateValue));
        $state = GameState::fromQueryValue($stateValue);

        $enemy = new Enemy(
            name: $node->enemyName,
            maxLifePoints: $node->enemyMaxLifePoints,
            lifePoints: $state->enemyLifePoints ?? $node->enemyMaxLifePoints,
            strength: $node->enemyStrength,
            agility: $node->enemyAgility,
        );

        $playerDice = new Dice()->rollDouble();
        $enemyDice = new Dice()->rollDouble();

        $result = Combat::resolveRound(
            player: $character->toCombatant(),
            enemy: $enemy->toCombatant(),
            playerDice: $playerDice,
            enemyDice: $enemyDice,
        );

        if ($result->hit === CombatHit::Player) {
            $enemy = $enemy->withDamage($result->damage);
        } elseif ($result->hit === CombatHit::Enemy) {
            $character = $character->withDamage($result->damage);
        }

        if ($enemy->isDefeated()) {
            return $this->redirect(
                url: ['controller' => 'Story', 'action' => 'chapter', $storyId, $node->targetVictory],
                query: ['state' => new GameState(player: $character)->toQueryValue()],
            );
        }

        if ($character->isDefeated()) {
            return $this->redirect(
                url: ['controller' => 'Story', 'action' => 'chapter', $storyId, $node->targetDefeat],
                query: ['state' => new GameState(player: $character)->toQueryValue()],
            );
        }

        $newState = new GameState(player: $character, enemyLifePoints: $enemy->lifePoints);

        $this->set([
            'game' => $game,
            'node' => $node,
            'enemy' => $enemy,
            'result' => $result,
            'character' => $character,
            'state' => $newState->toQueryValue(),
        ]);

        return null;
    }
}
