<?php
declare(strict_types=1);

namespace Test\Controller;

use App\Controller\StoryController;
use App\Story\Character;
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
     * A valid submission builds a `Character`, wraps it in a `GameState`, and redirects into `start()` with
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
            'The strength attribute must be at least 1, got `0`.',
            $controller->getView()->get('error'),
        );
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
}
