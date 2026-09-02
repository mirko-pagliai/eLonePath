<?php
declare(strict_types=1);

namespace TestApp\View;

use Elone\Core\View\View;

/**
 * A `View` subclass used to verify that `Controller::viewClass()` is respected — a controller overriding it should
 * end up with an instance of exactly this class, not the base `View`.
 */
final class CustomView extends View
{
}
