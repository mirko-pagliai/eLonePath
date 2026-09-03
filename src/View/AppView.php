<?php
declare(strict_types=1);

namespace App\View;

use App\View\Helper\StoryHelper;
use Elone\Core\View\View;

/**
 * The `View` every template in this app is rendered through — built by `AppController`.
 *
 * @property-read \App\View\Helper\StoryHelper $Story
 */
class AppView extends View
{
    public function __construct()
    {
        parent::__construct();

        $this->loadHelper(name: 'Story', helper: new StoryHelper());
    }
}
