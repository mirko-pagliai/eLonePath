<?php
declare(strict_types=1);

namespace Elone\Core\Test\Server;

use Elone\Core\Server\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * ResponseTest.
 */
#[CoversClass(Response::class)]
class ResponseTest extends TestCase
{
    /**
     * @link \Elone\Core\Server\Response::content()
     * @link \Elone\Core\Server\Response::status()
     */
    #[Test]
    public function testContentAndStatus(): void
    {
        $response = new Response('Hello', 201);

        $this->assertSame('Hello', $response->content());
        $this->assertSame(201, $response->status());
    }

    /**
     * @link \Elone\Core\Server\Response::content()
     * @link \Elone\Core\Server\Response::status()
     * @link \Elone\Core\Server\Response::headers()
     */
    #[Test]
    public function testDefaults(): void
    {
        $response = new Response();

        $this->assertSame('', $response->content());
        $this->assertSame(200, $response->status());
        $this->assertSame([], $response->headers());
    }

    /**
     * @link \Elone\Core\Server\Response::headers()
     */
    #[Test]
    public function testHeaders(): void
    {
        $response = new Response(status: 302, headers: ['Location' => '/pages/home']);

        $this->assertSame(302, $response->status());
        $this->assertSame(['Location' => '/pages/home'], $response->headers());
    }

    /**
     * @link \Elone\Core\Server\Response::send()
     */
    #[Test]
    public function testSend(): void
    {
        $response = new Response('Hello, world!', 201);

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertSame('Hello, world!', $output);
        $this->assertSame(201, http_response_code());
    }

    /**
     * `send()` calls PHP's own `header()`, but under the CLI SAPI (which is what PHPUnit runs under) `header()` never
     * populates `headers_list()` — verified directly against a real PHP CLI process, not assumed — so there's no way
     * to assert from here that the header was actually sent. This only re-confirms the status code still goes through
     * when headers are present; `testHeaders()` already covers that `Response` holds the right header values.
     *
     * @link \Elone\Core\Server\Response::send()
     */
    #[Test]
    public function testSendWithHeaders(): void
    {
        $response = new Response(status: 302, headers: ['Location' => '/pages/home']);

        ob_start();
        $response->send();
        ob_get_clean();

        $this->assertSame(302, http_response_code());
    }
}
