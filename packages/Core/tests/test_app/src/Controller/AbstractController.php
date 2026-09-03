<?php
declare(strict_types=1);

namespace TestApp\Controller;

use Elone\Core\Controller;

/**
 * An abstract controller — used to verify that `Route` rejects it as if it didn't exist.
 */
abstract class AbstractController extends Controller
{
    public function index(): void
    {
    }
}
