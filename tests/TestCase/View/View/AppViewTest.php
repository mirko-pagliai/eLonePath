<?php
declare(strict_types=1);

namespace Test\View;

use App\View\AppView;
use App\View\Helper\StoryHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * AppViewTest.
 */
#[CoversClass(AppView::class)]
class AppViewTest extends TestCase
{
    /**
     * @link \App\View\AppView::__construct()
     */
    #[Test]
    public function testConstructLoadsStoryHelper(): void
    {
        $view = new AppView();

        $this->assertInstanceOf(StoryHelper::class, $view->Story);
    }

    /**
     * `Html` keeps working exactly as it does on the base `View` — `AppView` only adds to it, it doesn't replace
     * anything.
     *
     * @link \App\View\AppView::__construct()
     */
    #[Test]
    public function testConstructKeepsHtmlHelper(): void
    {
        $view = new AppView();

        $this->assertSame('<i class="bi bi-github"></i>', $view->Html->icon('github'));
    }
}
