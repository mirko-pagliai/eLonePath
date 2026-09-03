<?php
declare(strict_types=1);

namespace Elone\Core\View\Helper;

use Elone\Core\View\View;

/**
 * Base class every helper extends. Takes the `View` itself, not any specific other helper, in its constructor —
 * the same way a template does. Any other helper this one needs (e.g. `$this->view->Html`) is reached lazily,
 * inside a method body, not at construction time: by the time any helper's own methods actually run, every helper
 * the view loads is already registered, regardless of which order they were loaded in — so this composes safely
 * even if helpers end up referencing each other in a cycle, which a constructor directly depending on another
 * helper's instance could not.
 *
 * Only a class extending this one can be registered via `View::loadHelper()` — the parameter type there enforces
 * it; `Helper` itself, being abstract, can never be instantiated directly.
 */
abstract class Helper
{
    public function __construct(protected readonly View $view)
    {
    }
}
