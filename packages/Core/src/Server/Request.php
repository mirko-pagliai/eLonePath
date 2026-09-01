<?php
declare(strict_types=1);

namespace Elone\Core\Server;

final class Request
{
    /**
     * @param array<string, mixed> $queryParams
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $queryParams = [],
    ) {
    }

    /**
     * Captures the current HTTP request method, URI, and query string, then returns a new instance of the class.
     *
     * It receives:
     * ```
     * REQUEST_URI = /pages/view/123?foo=bar
     * REQUEST_METHOD = GET
     * ```
     * and builds:
     * ```
     * new Request('GET', '/pages/view/123', ['foo' => 'bar'])
     * ```
     *
     * @return self An instance of the class initialized with the HTTP method, URI, and query parameters.
     */
    public static function capture(): self
    {
        $url = $_SERVER['REQUEST_URI'] ?? '/';
        assert(is_string($url));

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        assert(is_string($method));

        $uri = parse_url($url, PHP_URL_PATH) ?: '/';

        $query = parse_url($url, PHP_URL_QUERY);

        $queryParams = [];
        if (is_string($query)) {
            parse_str($query, $queryParams);
        }

        return new self(method: strtoupper($method), path: $uri, queryParams: $queryParams);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * @return array<string, mixed>
     */
    public function queryParams(): array
    {
        return $this->queryParams;
    }

    public function queryParam(string $name, mixed $default = null): mixed
    {
        return $this->queryParams[$name] ?? $default;
    }
}
