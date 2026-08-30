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
     */
    #[Test]
    public function testDefaults(): void
    {
        $response = new Response();

        $this->assertSame('', $response->content());
        $this->assertSame(200, $response->status());
    }
}
