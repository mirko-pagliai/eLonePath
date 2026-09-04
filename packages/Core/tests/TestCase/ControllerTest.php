<?php
declare(strict_types=1);

namespace Elone\Core\Test;

use Elone\Core\Controller;
use Elone\Core\Exception\MethodNotAllowedException;
use Elone\Core\Server\Request;
use Elone\Core\Server\Response;
use Elone\Core\View\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TestApp\View\CustomView;

/**
 * ControllerTest.
 */
#[CoversClass(Controller::class)]
class ControllerTest extends TestCase
{
    /**
     * With no `View` given and no `viewClass()` override, the base `View` is used — the existing, unchanged
     * default behavior.
     *
     * @link \Elone\Core\Controller::__construct()
     */
    #[Test]
    public function testConstructBuildsBaseViewByDefault(): void
    {
        $controller = new class extends Controller {
            public function getView(): View
            {
                return $this->view;
            }
        };

        $this->assertInstanceOf(View::class, $controller->getView());
    }

    /**
     * A subclass overriding `viewClass()` — the app's own `AppController`, in practice — gets an instance of that
     * class, not the base `View`, when no `View` is given explicitly.
     *
     * @link \Elone\Core\Controller::__construct()
     * @link \Elone\Core\Controller::viewClass()
     */
    #[Test]
    public function testConstructRespectsViewClassOverride(): void
    {
        $controller = new class extends Controller {
            protected static function viewClass(): string
            {
                return CustomView::class;
            }

            public function getView(): View
            {
                return $this->view;
            }
        };

        $this->assertInstanceOf(CustomView::class, $controller->getView());
    }

    /**
     * An explicitly given `View` is used as-is, regardless of `viewClass()`.
     *
     * @link \Elone\Core\Controller::__construct()
     */
    #[Test]
    public function testConstructWithExplicitViewIgnoresViewClass(): void
    {
        $view = new View();

        $controller = new class ($view) extends Controller {
            public function __construct(View $view)
            {
                parent::__construct(view: $view);
            }

            protected static function viewClass(): string
            {
                return CustomView::class;
            }

            public function getView(): View
            {
                return $this->view;
            }
        };

        $this->assertSame($view, $controller->getView());
    }

    /**
     * @link \Elone\Core\Controller::set()
     */
    #[Test]
    public function testSet(): void
    {
        $view = new class extends View {
            /**
             * @var array<string, mixed>
             */
            public array $data = [];
        };

        $controller = new class (view: $view) extends Controller {
            public function set(array $data): static
            {
                return parent::set($data);
            }

            public function getView(): View
            {
                return $this->view;
            }
        };

        $result = $controller->set(['key' => 'value']);
        $this->assertSame($controller, $result);

        $this->assertSame('value', $controller->getView()->get('key'));
    }

    /**
     * @link \Elone\Core\Controller::is()
     */
    #[Test]
    public function testIsDelegatesToTheRequest(): void
    {
        $request = new Request('POST', '/');

        $controller = new class (request: $request) extends Controller {
            public function is(string $type): bool
            {
                return parent::is($type);
            }
        };

        $this->assertTrue($controller->is('post'));
        $this->assertFalse($controller->is('get'));
    }

    /**
     * @link \Elone\Core\Controller::allowMethod()
     */
    #[Test]
    public function testAllowMethodDelegatesToTheRequest(): void
    {
        $request = new Request('GET', '/');

        $controller = new class (request: $request) extends Controller {
            public function allowMethod(array|string $methods): void
            {
                parent::allowMethod($methods);
            }
        };

        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessageIs('Method `GET` is not allowed. Allowed: `POST`.');
        $controller->allowMethod('post');
    }

    /**
     * @link \Elone\Core\Controller::queryParams()
     * @link \Elone\Core\Controller::queryParam()
     */
    #[Test]
    public function testQueryParams(): void
    {
        $request = new Request('GET', '/?foo=bar');

        $controller = new class (request: $request) extends Controller {
            public function queryParams(): array
            {
                return parent::queryParams();
            }

            public function queryParam(string $name, mixed $default = null): mixed
            {
                return parent::queryParam($name, $default);
            }
        };

        $this->assertSame(['foo' => 'bar'], $controller->queryParams());
        $this->assertSame('bar', $controller->queryParam('foo'));
        $this->assertNull($controller->queryParam('missing'));
        $this->assertSame('default', $controller->queryParam('missing', 'default'));
    }

    /**
     * @link \Elone\Core\Controller::redirect()
     */
    #[Test]
    public function testRedirectWithString(): void
    {
        $controller = new class extends Controller {
            public function redirect(array|string $url, int $status = 302): Response
            {
                return parent::redirect($url, $status);
            }
        };

        $response = $controller->redirect('/');

        $this->assertSame(302, $response->status());
        $this->assertSame(['Location' => '/'], $response->headers());
    }

    /**
     * @link \Elone\Core\Controller::redirect()
     */
    #[Test]
    public function testRedirectWithExternalUrl(): void
    {
        $controller = new class extends Controller {
            public function redirect(array|string $url, int $status = 302): Response
            {
                return parent::redirect($url, $status);
            }
        };

        $response = $controller->redirect('https://example.com');

        $this->assertSame(['Location' => 'https://example.com'], $response->headers());
    }

    /**
     * @link \Elone\Core\Controller::redirect()
     */
    #[Test]
    public function testRedirectWithRoute(): void
    {
        $controller = new class extends Controller {
            public function redirect(array|string $url, int $status = 302): Response
            {
                return parent::redirect($url, $status);
            }
        };

        $response = $controller->redirect(['controller' => 'Pages', 'action' => 'home']);

        $this->assertSame(['Location' => '/pages/home'], $response->headers());
    }

    /**
     * @link \Elone\Core\Controller::redirect()
     */
    #[Test]
    public function testRedirectWithCustomStatus(): void
    {
        $controller = new class extends Controller {
            public function redirect(array|string $url, int $status = 302): Response
            {
                return parent::redirect($url, $status);
            }
        };

        $response = $controller->redirect('/', 301);

        $this->assertSame(301, $response->status());
    }
}
