<?php
declare(strict_types=1);

namespace App\Controller;

use App\View\AppView;
use Elone\Core\Controller;

/**
 * The base of every controller in this app extends.
 */
abstract class AppController extends Controller
{
    protected static function viewClass(): string
    {
        return AppView::class;
    }
}
