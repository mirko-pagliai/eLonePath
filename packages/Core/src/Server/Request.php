<?php
declare(strict_types=1);

namespace Elone\Core\Server;

final class Request
{
    private readonly string $path;

    /**
     * @var array<string, mixed>
     */
    private readonly array $queryParams;

    public function __construct(private readonly string $method, string $uri)
    {
        $this->path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $query = parse_url($uri, PHP_URL_QUERY);

        $queryParams = [];
        if (is_string($query)) {
            parse_str($query, $queryParams);
        }

        $this->queryParams = $queryParams;
    }

    /**
     * Builds a `Request` from PHP's own superglobals (`$_SERVER['REQUEST_METHOD']` and `['REQUEST_URI']`),
     * defaulting to `GET /` if either is missing.
     */
    public static function fromGlobals(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        assert(is_string($uri));

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        assert(is_string($method));

        return new self(method: strtoupper($method), uri: $uri);
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
