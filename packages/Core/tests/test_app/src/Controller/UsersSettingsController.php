<?php
declare(strict_types=1);

namespace TestApp\Controller;

use Elone\Core\Controller;

/**
 * Multi-word controller name, used to verify that a kebab-case URL segment (`users-settings`) correctly resolves to
 * this PascalCase controller.
 */
class UsersSettingsController extends Controller
{
    public function index(): void
    {
    }

    /**
     * A multi-word action, used to verify that `Dispatcher::templateName()` snake_cases the action portion of the
     * template path (`some_action_name.php`), unlike the controller portion, which is used as-is.
     */
    public function someActionName(): void
    {
    }
}
