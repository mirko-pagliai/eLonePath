<?php
declare(strict_types=1);

namespace Elone\Core\Test;

use Elone\Core\Controller;
use Elone\Core\Server\Request;
use Elone\Core\Server\Response;
use Elone\Core\View\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * ControllerTest.
 */
#[CoversClass(Controller::class)]
class ControllerTest extends TestCase
{
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
