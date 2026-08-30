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
}
