<?php
declare(strict_types=1);

namespace TestApp\Controller;

use Elone\Core\Controller;

/**
 * An abstract controller — used to verify that `Route` rejects it as if it didn't exist (the app's own
 * `AppController` is the real-world example this stands in for), rather than letting it reach `Dispatcher`, which
 * would fail trying to instantiate it.
 */
abstract class AbstractController extends Controller
{
    public function index(): void
    {
    }
}
