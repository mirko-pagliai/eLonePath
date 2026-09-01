<?php
declare(strict_types=1);

namespace Elone\Core\Test\Server;

use Elone\Core\Server\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->originalServer = $_SERVER;
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
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
     * @link \Elone\Core\Server\Request::fromGlobals()
     */
    #[Test]
    public function testFromGlobals(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $_SERVER['REQUEST_URI'] = '/pages/view/123?foo=bar';

        $request = Request::fromGlobals();

        $this->assertSame('POST', $request->method());
        $this->assertSame('/pages/view/123', $request->path());
        $this->assertSame(['foo' => 'bar'], $request->queryParams());
    }

    /**
     * @link \Elone\Core\Server\Request::fromGlobals()
     */
    #[Test]
    public function testFromGlobalsWithMissingServerValues(): void
    {
        unset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

        $request = Request::fromGlobals();

        $this->assertSame('GET', $request->method());
        $this->assertSame('/', $request->path());
        $this->assertSame([], $request->queryParams());
    }
}
