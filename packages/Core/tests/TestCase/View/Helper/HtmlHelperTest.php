<?php
declare(strict_types=1);

namespace Elone\Core\Test\View\Helper;

use Elone\Core\Configuration;
use Elone\Core\Exception\RouteNotFoundException;
use Elone\Core\View\Helper\HtmlHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * HtmlHelperTest.
 */
#[CoversClass(HtmlHelper::class)]
class HtmlHelperTest extends TestCase
{
    private HtmlHelper $htmlHelper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $configuration = new Configuration(TEST_APP, 'TestApp');

        $this->htmlHelper = new HtmlHelper($configuration);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::url()
     */
    #[Test]
    #[TestWith([['controller' => 'Pages', 'action' => 'home'], '/pages/home'])]
    #[TestWith([['controller' => 'Pages', 'action' => 'view', '123'], '/pages/view/123'])]
    #[TestWith([['controller' => 'UsersSettings'], '/users-settings/index'])]
    public function testUrl(array $route, string $expected): void
    {
        $result = $this->htmlHelper->url($route);
        $this->assertSame($expected, $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::url()
     */
    #[Test]
    public function testUrlWithMissingController(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessageIs('Invalid route.');
        $this->htmlHelper->url([]);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::url()
     */
    #[Test]
    public function testUrlWithInvalidParameter(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessageIs('Invalid route parameter: `extra`.');
        $this->htmlHelper->url(['controller' => 'Pages', 'action' => 'home', 'extra' => 'value']);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::link()
     */
    #[Test]
    public function testLink(): void
    {
        $result = $this->htmlHelper->link('Home', ['controller' => 'Pages', 'action' => 'home']);
        $this->assertSame('<a href="/pages/home">Home</a>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\HtmlHelper::link()
     */
    #[Test]
    public function testLinkEscapesTextAndAttributes(): void
    {
        $result = $this->htmlHelper->link(
            'A & B',
            ['controller' => 'Pages', 'action' => 'home'],
            ['class' => 'btn "special"'],
        );
        $this->assertSame('<a href="/pages/home" class="btn &quot;special&quot;">A &amp; B</a>', $result);
    }
}
