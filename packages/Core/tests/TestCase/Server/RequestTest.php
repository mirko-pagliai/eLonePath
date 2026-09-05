<?php
declare(strict_types=1);

namespace Elone\Core\Test\Server;

use Elone\Core\Exception\MethodNotAllowedException;
use Elone\Core\Server\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * RequestTest.
 */
#[CoversClass(Request::class)]
class RequestTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $originalServer;

    /**
     * @var array<string, mixed>
     */
    private array $originalPost;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->originalServer = $_SERVER;
        $this->originalPost = $_POST;
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        $_POST = $this->originalPost;
    }

    /**
     * @link \Elone\Core\Server\Request::method()
     * @link \Elone\Core\Server\Request::path()
     */
    #[Test]
    public function testMethodAndPath(): void
    {
        $request = new Request('POST', '/pages/view/123');

        $this->assertSame('POST', $request->method());
        $this->assertSame('/pages/view/123', $request->path());
    }

    /**
     * @link \Elone\Core\Server\Request::__construct()
     * @link \Elone\Core\Server\Request::queryParams()
     * @link \Elone\Core\Server\Request::queryParam()
     */
    #[Test]
    public function testQueryParams(): void
    {
        $request = new Request('GET', '/pages/view/123?foo=bar');

        $this->assertSame('/pages/view/123', $request->path());
        $this->assertSame(['foo' => 'bar'], $request->queryParams());
        $this->assertSame('bar', $request->queryParam('foo'));
        $this->assertNull($request->queryParam('missing'));
        $this->assertSame('default', $request->queryParam('missing', 'default'));
    }

    /**
     * @link \Elone\Core\Server\Request::__construct()
     */
    #[Test]
    public function testWithoutQueryString(): void
    {
        $request = new Request('GET', '/pages/home');

        $this->assertSame([], $request->queryParams());
    }

    /**
     * `$data` defaults to empty — a plain GET, with nothing passed for the third constructor argument, has no
     * body fields at all.
     *
     * @link \Elone\Core\Server\Request::__construct()
     * @link \Elone\Core\Server\Request::data()
     * @link \Elone\Core\Server\Request::dataParam()
     */
    #[Test]
    public function testDataDefaultsToEmpty(): void
    {
        $request = new Request('GET', '/');

        $this->assertSame([], $request->data());
        $this->assertNull($request->dataParam('missing'));
        $this->assertSame('default', $request->dataParam('missing', 'default'));
    }

    /**
     * @link \Elone\Core\Server\Request::__construct()
     * @link \Elone\Core\Server\Request::data()
     * @link \Elone\Core\Server\Request::dataParam()
     */
    #[Test]
    public function testData(): void
    {
        $request = new Request('POST', '/story/character/mini-quest', ['strength' => '9', 'agility' => '5']);

        $this->assertSame(['strength' => '9', 'agility' => '5'], $request->data());
        $this->assertSame('9', $request->dataParam('strength'));
        $this->assertNull($request->dataParam('missing'));
        $this->assertSame('default', $request->dataParam('missing', 'default'));
    }

    /**
     * `data()` and `queryParams()` are deliberately independent — a value posted in the body doesn't leak into
     * the query params, and vice versa, even when a request genuinely has both (a POST to a URL that also
     * carries its own querystring, e.g. `?state=...`).
     *
     * @link \Elone\Core\Server\Request::__construct()
     * @link \Elone\Core\Server\Request::data()
     * @link \Elone\Core\Server\Request::queryParams()
     */
    #[Test]
    public function testDataAndQueryParamsAreIndependent(): void
    {
        $request = new Request('POST', '/story/character/mini-quest?state=abc123', ['strength' => '9']);

        $this->assertSame(['strength' => '9'], $request->data());
        $this->assertSame(['state' => 'abc123'], $request->queryParams());
    }

    /**
     * @link \Elone\Core\Server\Request::createFromGlobals()
     */
    #[Test]
    public function testCreateFromGlobals(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $_SERVER['REQUEST_URI'] = '/pages/view/123?foo=bar';

        $request = Request::createFromGlobals();

        $this->assertSame('POST', $request->method());
        $this->assertSame('/pages/view/123', $request->path());
        $this->assertSame(['foo' => 'bar'], $request->queryParams());
    }

    /**
     * `createFromGlobals()` populates `data()` from PHP's own `$_POST` — this is what a real POST submission
     * actually goes through, unlike every other test here, which builds a `Request` directly with `$data` given
     * by hand.
     *
     * @link \Elone\Core\Server\Request::createFromGlobals()
     * @link \Elone\Core\Server\Request::data()
     */
    #[Test]
    public function testCreateFromGlobalsPopulatesDataFromPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/story/character/mini-quest';
        $_POST = ['strength' => '9', 'agility' => '5'];

        $request = Request::createFromGlobals();

        $this->assertSame(['strength' => '9', 'agility' => '5'], $request->data());
    }

    /**
     * @link \Elone\Core\Server\Request::createFromGlobals()
     */
    #[Test]
    public function testCreateFromGlobalsWithMissingServerValues(): void
    {
        unset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

        $request = Request::createFromGlobals();

        $this->assertSame('GET', $request->method());
        $this->assertSame('/', $request->path());
        $this->assertSame([], $request->queryParams());
    }

    /**
     * @link \Elone\Core\Server\Request::is()
     */
    #[Test]
    #[TestWith(['GET', 'get', true])]
    #[TestWith(['GET', 'GET', true])]
    #[TestWith(['GET', 'post', false])]
    #[TestWith(['POST', 'post', true])]
    #[TestWith(['POST', 'get', false])]
    public function testIs(string $requestMethod, string $type, bool $expected): void
    {
        $request = new Request($requestMethod, '/');

        $this->assertSame($expected, $request->is($type));
    }

    /**
     * @link \Elone\Core\Server\Request::allowMethod()
     */
    #[Test]
    public function testAllowMethodWithMatchingSingleMethod(): void
    {
        $request = new Request('POST', '/');

        // No exception expected.
        $request->allowMethod('post');
        $this->addToAssertionCount(1);
    }

    /**
     * @link \Elone\Core\Server\Request::allowMethod()
     */
    #[Test]
    public function testAllowMethodWithMatchingMethodInList(): void
    {
        $request = new Request('POST', '/');

        // No exception expected.
        $request->allowMethod(['get', 'post']);
        $this->addToAssertionCount(1);
    }

    /**
     * @link \Elone\Core\Server\Request::allowMethod()
     */
    #[Test]
    public function testAllowMethodWithSingleMethodMismatch(): void
    {
        $request = new Request('GET', '/');

        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessageIs('Method `GET` is not allowed. Allowed: `POST`.');
        $request->allowMethod('post');
    }

    /**
     * @link \Elone\Core\Server\Request::allowMethod()
     */
    #[Test]
    public function testAllowMethodWithMultipleMethodsMismatch(): void
    {
        $request = new Request('GET', '/');

        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessageIs('Method `GET` is not allowed. Allowed: `POST`, `DELETE`.');
        $request->allowMethod(['post', 'delete']);
    }

    /**
     * @link \Elone\Core\Server\Request::allowMethod()
     */
    #[Test]
    public function testAllowMethodThrowsWithStatus405(): void
    {
        $request = new Request('GET', '/');

        try {
            $request->allowMethod('post');
            $this->fail('Expected a MethodNotAllowedException to be thrown.');
        } catch (MethodNotAllowedException $exception) {
            $this->assertSame(405, $exception->statusCode());
        }
    }
}
