<?php
declare(strict_types=1);

namespace App\View;

use App\View\Helper\StoryHelper;
use Elone\Core\Server\Request;
use Elone\Core\View\Helper\AlertHelper;
use Elone\Core\View\Helper\FormHelper;
use Elone\Core\View\Helper\HtmlHelper;
use Elone\Core\View\View;

/**
 * The `View` every template in this app is rendered through — built by `AppController`.
 *
 * @property-read \Elone\Core\View\Helper\HtmlHelper $Html
 * @property-read \App\View\Helper\StoryHelper $Story
 * @property-read \Elone\Core\View\Helper\FormHelper $Form
 * @property-read \Elone\Core\View\Helper\AlertHelper $Alert
 */
class AppView extends View
{
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);

        $this->loadHelper(name: 'Html', helper: new HtmlHelper($this));
        $this->loadHelper(name: 'Story', helper: new StoryHelper($this));
        $this->loadHelper(name: 'Form', helper: new FormHelper($this));
        $this->loadHelper(name: 'Alert', helper: new AlertHelper($this));
    }
}
