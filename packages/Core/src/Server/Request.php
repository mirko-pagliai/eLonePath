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

    /**
     * @param string $method The HTTP method, e.g. `GET`, `POST`. Stored exactly as given — normalizing it (e.g.
     *  to uppercase) is the caller's responsibility; `createFromGlobals()` does it before calling this.
     * @param string $uri The raw request URI — path and, optionally, query string together, e.g.
     *  `/pages/view/123?foo=bar`. Parsed once, here, into what `path()` and `queryParams()` return.
     * @return void
     */
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
     *
     * @return self A `Request` reflecting the current PHP superglobals.
     */
    public static function createFromGlobals(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        assert(is_string($uri));

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        assert(is_string($method));

        return new self(strtoupper($method), $uri);
    }

    /**
     * @return string The HTTP method, e.g. `GET`, `POST`.
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * @return string The request path, without the query string.
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * @return array<string, mixed> The query string parameters, as an associative array.
     */
    public function queryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @param string $name The parameter name to look up.
     * @param mixed $default The value to return if `$name` isn't present.
     * @return mixed The parameter's value, or `$default`.
     */
    public function queryParam(string $name, mixed $default = null): mixed
    {
        return $this->queryParams[$name] ?? $default;
    }
}
