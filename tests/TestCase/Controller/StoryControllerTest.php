<?php
declare(strict_types=1);

namespace Test\Controller;

use App\Controller\StoryController;
use App\Story\Character;
use App\Story\Combat\CombatRoundResult;
use App\Story\Enemy;
use App\Story\GameState;
use App\View\AppView;
use Elone\Core\Exception\HttpException;
use Elone\Core\Exception\MethodNotAllowedException;
use Elone\Core\Server\Request;
use Elone\Core\Server\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * StoryControllerTest.
 */
#[CoversClass(StoryController::class)]
class StoryControllerTest extends TestCase
{
    /**
     * `StoryController` isn't anonymous-subclassed for every test the way `ControllerTest`'s targets are — it's
     * a concrete class most tests use as-is — but a handful still need `getView()` to inspect what an action
     * `set()`, the same pattern `ControllerTest` already uses.
     */
    private function makeController(Request $request): StoryController
    {
        return new class ($request) extends StoryController {
            public function getView(): AppView
            {
                /** @var \App\View\AppView $view */
                $view = $this->view;

                return $view;
            }
        };
    }

    private function samplePlayer(): Character
    {
        return Character::createNew(maxLifePoints: 20, strength: 9, agility: 5, perception: 3, willpower: 3);
    }

    /**
     * @link \App\Controller\StoryController::character()
     */
    #[Test]
    public function testCharacterShowsFormOnGet(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/character/mini-quest'));

        $result = $controller->character('mini-quest');

        $this->assertNull($result);
        $this->assertSame('mini-quest', $controller->getView()->get('game')->gameId);
        $this->assertNull($controller->getView()->get('error'));
    }

    /**
     * @link \App\Controller\StoryController::character()
     */
    #[Test]
    public function testCharacterRejectsOtherMethods(): void
    {
        $controller = $this->makeController(new Request('DELETE', '/story/character/mini-quest'));

        $this->expectException(MethodNotAllowedException::class);
        $controller->character('mini-quest');
    }

    /**
     * A valid-game submission builds a `Character`, wraps it in a `GameState`, and redirects into `start()` with
     * `?state=` carrying exactly the submitted values — this is the one behavior the whole character-creation
     * flow exists for.
     *
     * @link \App\Controller\StoryController::character()
     */
    #[Test]
    public function testCharacterWithValidDataRedirectsWithState(): void
    {
        $request = new Request('POST', '/story/character/mini-quest', [
            'strength' => '9',
            'agility' => '5',
            'perception' => '3',
            'willpower' => '3',
        ]);
        $controller = $this->makeController($request);

        $response = $controller->character('mini-quest');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(302, $response->status());

        $location = $response->headers()['Location'];
        $this->assertStringStartsWith('/story/start/mini-quest?state=', $location);

        parse_str((string)parse_url($location, PHP_URL_QUERY), $query);
        $state = GameState::fromQueryValue($query['state']);
        $this->assertSame(9, $state->player->strength);
        $this->assertSame(5, $state->player->agility);
        $this->assertSame(3, $state->player->perception);
        $this->assertSame(3, $state->player->willpower);
        $this->assertSame(20, $state->player->maxLifePoints);
    }

    /**
     * An invalid submission (the four attributes don't sum to 20) doesn't redirect — it re-renders the form,
     * carrying `Character`'s own validation message as `error`, rather than a generic one.
     *
     * @link \App\Controller\StoryController::character()
     */
    #[Test]
    public function testCharacterWithInvalidSumShowsError(): void
    {
        $request = new Request('POST', '/story/character/mini-quest', [
            'strength' => '10',
            'agility' => '5',
            'perception' => '3',
            'willpower' => '3',
        ]);
        $controller = $this->makeController($request);

        $result = $controller->character('mini-quest');

        $this->assertNull($result);
        $this->assertSame(
            "The sum of the character's attributes must be 20, got `21`.",
            $controller->getView()->get('error'),
        );
    }

    /**
     * A missing/non-numeric field (nothing submitted for `strength`, here) is treated as `0` — which
     * `Character::createNew()` itself then rejects with its own clear message, rather than this action crashing
     * on a type mismatch.
     *
     * @link \App\Controller\StoryController::character()
     */
    #[Test]
    public function testCharacterWithMissingFieldShowsError(): void
    {
        $request = new Request('POST', '/story/character/mini-quest', [
            'agility' => '5',
            'perception' => '3',
            'willpower' => '3',
        ]);
        $controller = $this->makeController($request);

        $result = $controller->character('mini-quest');

        $this->assertNull($result);
        $this->assertSame(
            'The strength attribute must be between 4 and 10, got `0`.',
            $controller->getView()->get('error'),
        );
    }

    /**
     * `random()` doesn't know how a random distribution gets picked — that's `Character::createRandom()`'s own
     * business, tested exhaustively there. This only checks the wiring around it: the result is wrapped in a
     * `GameState` and redirected into `start()`, exactly like a manual submission.
     *
     * @link \App\Controller\StoryController::random()
     */
    #[Test]
    public function testRandomRedirectsWithValidState(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/random/mini-quest'));

        $response = $controller->random('mini-quest');

        $this->assertSame(302, $response->status());

        $location = $response->headers()['Location'];
        $this->assertStringStartsWith('/story/start/mini-quest?state=', $location);

        parse_str((string)parse_url($location, PHP_URL_QUERY), $query);
        $state = GameState::fromQueryValue($query['state']);
        $this->assertSame(Character::DEFAULT_MAX_LIFE_POINTS, $state->player->maxLifePoints);
    }

    /**
     * `mini-quest` has a preface — `start()` sets `game` for the template to show it, and doesn't redirect.
     *
     * @link \App\Controller\StoryController::start()
     */
    #[Test]
    public function testStartWithPrefaceDoesNotRedirect(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/start/mini-quest'));

        $result = $controller->start('mini-quest');

        $this->assertNull($result);
        $this->assertSame('mini-quest', $controller->getView()->get('game')->gameId);
    }

    /**
     * `no-preface` has no preface — `start()` redirects straight to chapter 1 instead of rendering anything
     * itself.
     *
     * @link \App\Controller\StoryController::start()
     */
    #[Test]
    public function testStartWithoutPrefaceRedirectsToChapterOne(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/start/no-preface'));

        $response = $controller->start('no-preface');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('/story/chapter/no-preface/1', $response->headers()['Location']);
    }

    /**
     * The no-preface redirect carries `?state=` forward too — a player who just created a character for a story
     * with no preface must not lose their character on this very first hop.
     *
     * @link \App\Controller\StoryController::start()
     */
    #[Test]
    public function testStartWithoutPrefacePropagatesStateToRedirect(): void
    {
        $state = new GameState(player: $this->samplePlayer());
        $controller = $this->makeController(
            new Request('GET', "/story/start/no-preface?state={$state->toQueryValue()}"),
        );

        $response = $controller->start('no-preface');

        $this->assertSame(
            '/story/chapter/no-preface/1?' . http_build_query(['state' => $state->toQueryValue()]),
            $response->headers()['Location'],
        );
    }

    /**
     * With no `?state=` at all (a story that doesn't go through character creation, today's only real case),
     * `start()`'s redirect carries nothing — not even an empty `?state=`.
     *
     * @link \App\Controller\StoryController::start()
     */
    #[Test]
    public function testStartWithoutPrefaceAndWithoutStateRedirectsWithNoQuery(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/start/no-preface'));

        $response = $controller->start('no-preface');

        $this->assertSame('/story/chapter/no-preface/1', $response->headers()['Location']);
    }

    /**
     * @link \App\Controller\StoryController::chapter()
     */
    #[Test]
    public function testChapterSetsGameAndNode(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/chapter/mini-quest/1'));

        $controller->chapter('mini-quest', 1);

        $this->assertSame('mini-quest', $controller->getView()->get('game')->gameId);
        $this->assertSame(1, $controller->getView()->get('node')->id);
    }

    /**
     * With no `?state=` present, `chapter()` still renders — `character` is simply `null`, for the template to
     * skip the character sheet entirely.
     *
     * @link \App\Controller\StoryController::chapter()
     */
    #[Test]
    public function testChapterWithoutStateHasNullCharacter(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/chapter/mini-quest/1'));

        $controller->chapter('mini-quest', 1);

        $this->assertNull($controller->getView()->get('character'));
        $this->assertNull($controller->getView()->get('state'));
    }

    /**
     * With `?state=` present, `chapter()` decodes it into the `character` the template shows, and keeps the raw
     * value under `state` for `StoryHelper::link()` to carry forward into this page's own links.
     *
     * @link \App\Controller\StoryController::chapter()
     * @link \App\Controller\StoryController::propagateState()
     */
    #[Test]
    public function testChapterWithStateSetsCharacter(): void
    {
        $state = new GameState(player: $this->samplePlayer());
        $controller = $this->makeController(
            new Request('GET', "/story/chapter/mini-quest/1?state={$state->toQueryValue()}"),
        );

        $controller->chapter('mini-quest', 1);

        $character = $controller->getView()->get('character');
        $this->assertInstanceOf(Character::class, $character);
        $this->assertSame(9, $character->strength);
        $this->assertSame($state->toQueryValue(), $controller->getView()->get('state'));
    }

    /**
     * A node number that doesn't exist in the story surfaces `Game::getNode()`'s own `HttpException` — `chapter()`
     * doesn't add its own handling on top.
     *
     * @link \App\Controller\StoryController::chapter()
     */
    #[Test]
    public function testChapterWithMissingNodeThrows(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/chapter/mini-quest/99'));

        $this->expectException(HttpException::class);
        $controller->chapter('mini-quest', 99);
    }

    /**
     * @link \App\Controller\StoryController::roll()
     */
    #[Test]
    public function testRollSetsExpectedData(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/roll/mini-quest/2'));

        $controller->roll('mini-quest', 2);

        // node 2's dice check: minimum 1, so any roll (1-6) succeeds, landing on target_success (3).
        $this->assertTrue($controller->getView()->get('success'));
        $this->assertSame(3, $controller->getView()->get('target'));
        $this->assertCount(1, $controller->getView()->get('rolls'));
        $this->assertGreaterThanOrEqual(1, $controller->getView()->get('total'));
    }

    /**
     * `roll()` propagates state the same way `chapter()` does — the character survives a dice check too, not
     * just plain passages.
     *
     * @link \App\Controller\StoryController::roll()
     */
    #[Test]
    public function testRollWithStateSetsCharacter(): void
    {
        $state = new GameState(player: $this->samplePlayer());
        $controller = $this->makeController(
            new Request('GET', "/story/roll/mini-quest/2?state={$state->toQueryValue()}"),
        );

        $controller->roll('mini-quest', 2);

        $character = $controller->getView()->get('character');
        $this->assertInstanceOf(Character::class, $character);
        $this->assertSame(9, $character->strength);
    }

    /**
     * `roll()` only makes sense for a `DiceNode` — calling it against, say, the passage at node 1, throws
     * instead of silently doing nothing.
     *
     * @link \App\Controller\StoryController::roll()
     */
    #[Test]
    public function testRollWithNonDiceNodeThrows(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/roll/mini-quest/1'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Node `1` in `mini-quest` is not a dice check.');
        $controller->roll('mini-quest', 1);
    }

    /**
     * `combat()` only makes sense for a `CombatNode` — calling it against, say, the passage at node 1, throws
     * instead of silently doing nothing.
     *
     * @link \App\Controller\StoryController::combat()
     */
    #[Test]
    public function testCombatWithNonCombatNodeThrows(): void
    {
        $state = new GameState(player: $this->samplePlayer());
        $controller = $this->makeController(
            new Request('GET', "/story/combat/mini-quest/1?state={$state->toQueryValue()}"),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Node `1` in `mini-quest` is not a combat.');
        $controller->combat('mini-quest', 1);
    }

    /**
     * A fight can't be resolved without a player `Combatant` to resolve it against — reaching this action with
     * no `?state=` at all (skipping character creation) is a navigation mistake, not something to recover from
     * silently.
     *
     * @link \App\Controller\StoryController::combat()
     */
    #[Test]
    public function testCombatWithoutCharacterThrows(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/combat/combat-quest/1'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('No character found for `combat-quest` — create one before fighting.');
        $controller->combat('combat-quest', 1);
    }

    /**
     * One round, whatever its real-dice outcome, either redirects (the round ended the fight) or leaves the
     * view holding a consistent, fully-typed set of round data — this doesn't pin down *who* wins, since that
     * depends on genuine randomness `combat()` has no way to fake for a test (a public action reached by URL
     * can't take an extra injectable-dice parameter: `Dispatcher` requires the URL's own parameter count to
     * match exactly). What it proves is that the wiring between `Combat::resolveRound()` and the controller's
     * own state handling doesn't fall over either way.
     *
     * @link \App\Controller\StoryController::combat()
     */
    #[Test]
    public function testCombatRendersOrRedirectsConsistently(): void
    {
        $state = new GameState(player: $this->samplePlayer());
        $controller = $this->makeController(
            new Request('GET', "/story/combat/combat-quest/1?state={$state->toQueryValue()}"),
        );

        $response = $controller->combat('combat-quest', 1);

        if ($response !== null) {
            $location = $response->headers()['Location'];
            $this->assertTrue(
                str_starts_with($location, '/story/chapter/combat-quest/2')
                || str_starts_with($location, '/story/chapter/combat-quest/3'),
                "Unexpected redirect target: $location",
            );

            return;
        }

        $this->assertInstanceOf(Enemy::class, $controller->getView()->get('enemy'));
        $this->assertInstanceOf(CombatRoundResult::class, $controller->getView()->get('result'));
        $this->assertInstanceOf(Character::class, $controller->getView()->get('character'));
        $this->assertIsString($controller->getView()->get('state'));
    }

    /**
     * Plays a full fight out for real — the actual controller action, round after round, following the state
     * exactly as a browser would via the "continue fighting" link — and checks only that it always terminates,
     * and always at one of the node's own two declared endings. Which one is genuinely up to the dice.
     *
     * @link \App\Controller\StoryController::combat()
     */
    #[Test]
    public function testCombatEventuallyEndsInVictoryOrDefeat(): void
    {
        $stateValue = (new GameState(player: $this->samplePlayer()))->toQueryValue();

        for ($round = 0; $round < 50; $round++) {
            $controller = $this->makeController(
                new Request('GET', "/story/combat/combat-quest/1?state=$stateValue"),
            );

            $response = $controller->combat('combat-quest', 1);

            if ($response !== null) {
                $location = $response->headers()['Location'];
                $this->assertTrue(
                    str_starts_with($location, '/story/chapter/combat-quest/2')
                    || str_starts_with($location, '/story/chapter/combat-quest/3'),
                    "Unexpected redirect target: $location",
                );

                return;
            }

            $nextState = $controller->getView()->get('state');
            $this->assertIsString($nextState);
            $stateValue = $nextState;
        }

        $this->fail('Combat did not conclude within 50 rounds.');
    }

    /**
     * The enemy's life points travel through `GameState`, not the node — passing an already-reduced value in
     * proves `combat()` actually reads it, rather than always starting fresh at the node's own full health.
     *
     * @link \App\Controller\StoryController::combat()
     */
    #[Test]
    public function testCombatReadsEnemyLifePointsFromState(): void
    {
        $state = new GameState(player: $this->samplePlayer(), enemyLifePoints: 3);
        $controller = $this->makeController(
            new Request('GET', "/story/combat/combat-quest/1?state={$state->toQueryValue()}"),
        );

        $response = $controller->combat('combat-quest', 1);

        if ($response !== null) {
            // Defeated this very round — consistent with having started at only 3 life points, not a fresh 10.
            $this->assertStringStartsWith('/story/chapter/combat-quest/2', $response->headers()['Location']);

            return;
        }

        $enemy = $controller->getView()->get('enemy');
        $this->assertSame(10, $enemy->maxLifePoints);
        $this->assertLessThanOrEqual(3, $enemy->lifePoints);
    }

    /**
     * The counterpart to `testCombatReadsEnemyLifePointsFromState()`: a player already at 1 life point, reading
     * from a hand-built `Character` (the same technique `Character`'s own docblock describes for reconstructing
     * mid-story state), is defeated by essentially any hit at all. This alone doesn't force a specific outcome —
     * the same lucky roll that would defeat the player could instead land a big enough hit on the enemy to end
     * the fight the *other* way, since a single hit's damage isn't bounded by the enemy's own life points — but
     * whichever of the three outcomes occurs (defeat, victory, or the fight continuing with the player still at
     * 1) is what actually exercises the "player defeated" redirect branch across enough runs, which
     * `testCombatEventuallyEndsInVictoryOrDefeat()` alone might never reach by chance if the fixture's own stats
     * happen to favor the player, as they currently do.
     *
     * @link \App\Controller\StoryController::combat()
     */
    #[Test]
    public function testCombatWithLowPlayerLifePointsCanEndInDefeat(): void
    {
        $nearlyDefeatedPlayer = new Character(
            maxLifePoints: 20,
            lifePoints: 1,
            strength: 9,
            agility: 5,
            perception: 3,
            willpower: 3,
        );
        $state = new GameState(player: $nearlyDefeatedPlayer);
        $controller = $this->makeController(
            new Request('GET', "/story/combat/combat-quest/1?state={$state->toQueryValue()}"),
        );

        $response = $controller->combat('combat-quest', 1);

        if ($response !== null) {
            $location = $response->headers()['Location'];
            $this->assertTrue(
                str_starts_with($location, '/story/chapter/combat-quest/2')
                || str_starts_with($location, '/story/chapter/combat-quest/3'),
                "Unexpected redirect target: $location",
            );

            return;
        }

        // Neither side was defeated this round — a parry, or the player landed a hit that didn't finish the
        // enemy off. Either way, the player wasn't the one hit, so they're still at their starting 1 life point.
        $this->assertSame(1, $controller->getView()->get('character')->lifePoints);
    }

    /**
     * None of the tests above call `render()` — `combat.php` was missing for several turns without any of them
     * noticing. These do: for each action that falls through to `Dispatcher`'s own render step, confirm the
     * template it would use actually exists and evaluates without throwing. Not checking the rendered content
     * against what the action `set()` — that's a second, much larger claim this doesn't make.
     *
     * @link \App\Controller\StoryController::character()
     */
    #[Test]
    public function testCharacterTemplateExists(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/character/mini-quest'));
        $controller->character('mini-quest');

        $result = $controller->render('Story/character', layout: null);

        $this->assertNotSame('', trim($result));
    }

    /**
     * @link \App\Controller\StoryController::start()
     */
    #[Test]
    public function testStartTemplateExists(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/start/mini-quest'));
        $controller->start('mini-quest');

        $result = $controller->render('Story/start', layout: null);

        $this->assertNotSame('', trim($result));
    }

    /**
     * @link \App\Controller\StoryController::chapter()
     */
    #[Test]
    public function testChapterTemplateExists(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/chapter/mini-quest/1'));
        $controller->chapter('mini-quest', 1);

        $result = $controller->render('Story/chapter', layout: null);

        $this->assertNotSame('', trim($result));
    }

    /**
     * @link \App\Controller\StoryController::roll()
     */
    #[Test]
    public function testRollTemplateExists(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/roll/mini-quest/2'));
        $controller->roll('mini-quest', 2);

        $result = $controller->render('Story/roll', layout: null);

        $this->assertNotSame('', trim($result));
    }

    /**
     * `combat()` uses real dice — it might redirect (the round ended the fight) instead of rendering. Retries
     * with a fresh state until one round doesn't, rather than asserting on a specific, unforceable outcome.
     *
     * @link \App\Controller\StoryController::combat()
     */
    #[Test]
    public function testCombatTemplateExists(): void
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $state = new GameState(player: $this->samplePlayer());
            $controller = $this->makeController(
                new Request('GET', "/story/combat/combat-quest/1?state={$state->toQueryValue()}"),
            );

            if ($controller->combat('combat-quest', 1) === null) {
                $result = $controller->render('Story/combat', layout: null);
                $this->assertNotSame('', trim($result));

                return;
            }
        }

        $this->fail('Combat ended on every one of 10 attempts before a round could be rendered.');
    }

    /**
     * The bug this fixes: visiting a chapter URL directly, with no `?state=` at all, used to render fine even
     * for a story that needs a character — nothing stopped it. Now it redirects to `character()` instead.
     *
     * @link \App\Controller\StoryController::chapter()
     */
    #[Test]
    public function testChapterRedirectsToCharacterWhenStoryRequiresOneAndNoneIsPresent(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/chapter/requires-character/1'));

        $response = $controller->chapter('requires-character', 1);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('/story/character/requires-character', $response->headers()['Location']);
    }

    /**
     * @link \App\Controller\StoryController::chapter()
     */
    #[Test]
    public function testChapterRendersNormallyWhenStoryRequiresCharacterAndOneIsPresent(): void
    {
        $state = new GameState(player: $this->samplePlayer());
        $controller = $this->makeController(
            new Request('GET', "/story/chapter/requires-character/1?state={$state->toQueryValue()}"),
        );

        $response = $controller->chapter('requires-character', 1);

        $this->assertNull($response);
        $this->assertNotNull($controller->getView()->get('node'));
    }

    /**
     * A story that doesn't require a character is unaffected — this is what actually proves the check doesn't
     * fire indiscriminately, only for the stories that opted into it.
     *
     * @link \App\Controller\StoryController::chapter()
     */
    #[Test]
    public function testChapterRendersNormallyWhenStoryDoesNotRequireACharacter(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/chapter/mini-quest/1'));

        $response = $controller->chapter('mini-quest', 1);

        $this->assertNull($response);
    }

    /**
     * @link \App\Controller\StoryController::roll()
     */
    #[Test]
    public function testRollRedirectsToCharacterWhenStoryRequiresOneAndNoneIsPresent(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/roll/requires-character/1'));

        $response = $controller->roll('requires-character', 1);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('/story/character/requires-character', $response->headers()['Location']);
    }

    /**
     * @link \App\Controller\StoryController::start()
     */
    #[Test]
    public function testStartRedirectsToCharacterWhenStoryRequiresOneAndNoneIsPresent(): void
    {
        $controller = $this->makeController(new Request('GET', '/story/start/requires-character'));

        $response = $controller->start('requires-character');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('/story/character/requires-character', $response->headers()['Location']);
    }
}
