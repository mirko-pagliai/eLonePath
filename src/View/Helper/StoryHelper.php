<?php
declare(strict_types=1);

namespace App\View\Helper;

use Elone\Core\View\Helper\Helper;

/**
 * @property-read \Elone\Core\View\Helper\HtmlHelper $Html
 */
final class StoryHelper extends Helper
{
    /**
     * Wraps `Html->link()`, automatically carrying the current player's game state forward in the URL — every
     * in-story navigation link (a choice, a dice-roll prompt, "continue" after a roll) needs this, or the
     * player's character is lost the moment they click. The state comes from whatever the controller action
     * `set()` under the `state` key (see `App\Controller\StoryController::propagateState()`) — this helper
     * doesn't read the request itself, it only knows to look for that one key.
     *
     * Use `Html->link()` directly instead for anything that leaves the story rather than continuing it — e.g.
     * "back to the homepage" after a victory — which shouldn't carry state along.
     *
     * @param string $text The text (or, with `escape: false`, raw HTML) to display within the anchor tag.
     * @param array<string|int, string|int|float|bool>|string $url A literal URL/path, or a route array.
     * @param array<string, string|int|float|bool> $options Extra HTML attributes for the `<a>` tag — see
     *  `Html->link()`.
     * @param array<string, string|int|float|bool> $query Appended alongside the current state. An explicit
     *  `state` key here overrides it.
     * @return string The rendered HTML `<a>` tag.
     * @throws \Elone\Core\Exception\RouteNotFoundException If `$url` is an array route with invalid or missing
     *  parameters.
     */
    public function link(string $text, array|string $url, array $options = [], array $query = []): string
    {
        $state = $this->view->get('state');

        if (is_string($state)) {
            $query = ['state' => $state, ...$query];
        }

        return $this->Html->link($text, $url, $options, $query);
    }
}
