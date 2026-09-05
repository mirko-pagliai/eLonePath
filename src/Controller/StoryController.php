<?php
declare(strict_types=1);

namespace App\Controller;

use App\Story\Character;
use App\Story\Game;
use App\Story\GameState;
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
     * The player's starting maximum life points — fixed here, arbitrarily, until character creation says
     * otherwise (rolled, derived from an attribute, chosen by the player, or something else entirely). Not part
     * of the 20-point attribute budget `Character` itself enforces.
     */
    private const int STARTING_MAX_LIFE_POINTS = 20;

    /**
     * @param string $storyId The identifier of the story.
     * @return \App\Story\Game The game instance created from the specified story file.
     */
    protected function getGame(string $storyId): Game
    {
        return Game::createFromFile(STORIES . "/$storyId/story.json");
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
                    maxLifePoints: self::STARTING_MAX_LIFE_POINTS,
                    strength: (int)$this->dataParam('strength', 0),
                    agility: (int)$this->dataParam('agility', 0),
                    perception: (int)$this->dataParam('perception', 0),
                    willpower: (int)$this->dataParam('willpower', 0),
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
        $this->propagateState();
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
     * @return void
     * @link templates/Story/chapter.php
     */
    public function chapter(string $storyId, int $nodeNumber): void
    {
        $game = $this->getGame($storyId);
        $node = $game->getNode($nodeNumber);
        $this->propagateState();

        $this->set(compact('game', 'node'));
    }

    /**
     * Rolls the dice for a `DiceNode` and shows the outcome, with a link to whichever node it points to.
     *
     * @param string $storyId
     * @param int $nodeNumber
     * @return void
     * @throws \Random\RandomException
     * @link templates/Story/roll.php
     */
    public function roll(string $storyId, int $nodeNumber): void
    {
        $game = $this->getGame($storyId);
        $node = $game->getNode($nodeNumber);
        $this->propagateState();

        if (!$node instanceof DiceNode) {
            throw new RuntimeException("Node `$nodeNumber` in `$storyId` is not a dice check.");
        }

        $rolls = new Dice()->rollMultiple($node->requiredRolls);
        $total = array_sum($rolls);
        $success = $node->isSuccess($total);
        $target = $node->targetFor($total);

        $this->set(compact('game', 'rolls', 'total', 'success', 'target'));
    }
}
