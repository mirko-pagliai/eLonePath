<?php
declare(strict_types=1);

namespace Elone\Core\View\Helper;

use Elone\Core\View\View;

abstract class Helper
{
    public function __construct(private readonly View $view)
    {
    }
}
