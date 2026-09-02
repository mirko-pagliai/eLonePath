<?php
declare(strict_types=1);

namespace App\View;

use App\View\Helper\StoryHelper;
use Elone\Core\View\View;

/**
 * The `View` every template in this app is rendered through — built by `AppController`.
 */
class AppView extends View
{
    public function __construct()
    {
        parent::__construct();

        $this->loadHelper('Story', new StoryHelper());
    }
}
