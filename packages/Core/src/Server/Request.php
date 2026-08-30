<?php
declare(strict_types=1);

namespace Elone\Core\Server;

final class Request
{
    public function __construct(private readonly string $method, private readonly string $path)
    {
    }

    /**
     * Captures the current HTTP request method and URI, then returns a new instance of the class.
     *
     * It receives:
     * ```
     * REQUEST_URI = /pages/view/123
     * REQUEST_METHOD = GET
     * ```
     * and builds:
     * ```
     * new Request('GET', '/pages/view/123')
     * ```
     *
     * @return self An instance of the class initialized with the HTTP method and URI.
     */
    public static function capture(): self
    {
        $url = $_SERVER['REQUEST_URI'] ?? '/';
        assert(is_string($url));

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        assert(is_string($method));

        $uri = parse_url($url, PHP_URL_PATH) ?: '/';

        return new self(strtoupper($method), $uri);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }
}
