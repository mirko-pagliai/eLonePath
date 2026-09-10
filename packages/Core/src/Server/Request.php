<?php
declare(strict_types=1);

namespace Elone\Core\Server;

use Elone\Core\Exception\MethodNotAllowedException;

/**
 * Represents an HTTP request, encapsulating details such as the HTTP method, URI, query string, headers,
 * and request body data. Provides utility methods for accessing and validating these details.
 */
final class Request
{
    private readonly string $path;

    /**
     * @var array<array-key, mixed>
     */
    private readonly array $queryParams;

    /**
     * Creates a new `Request` from an HTTP method, a raw URI, optional body data, and optional headers.
     *
     * @param string $method The HTTP method, e.g. `GET`, `POST`. Stored exactly as given — normalizing it (e.g.
     *  to uppercase) is the caller's responsibility; `createFromGlobals()` does it before calling this.
     * @param string $uri The raw request URI — path and, optionally, query string together, e.g.
     *  `/pages/view/123?foo=bar`. Parsed once, here, into what `path()` and `queryParams()` return.
     * @param array<array-key, mixed> $data The parsed request body — form fields from a POST submission.
     *  `createFromGlobals()` populates this from PHP's own `$_POST`; empty (the default) for a request with no
     *  body, such as a plain GET.
     * @param array<string, string> $headers Request headers, keyed by their standard name (e.g. `Accept-Language`,
     *  not `HTTP_ACCEPT_LANGUAGE`) — `createFromGlobals()` does that normalization from `$_SERVER` before calling
     *  this. Empty (the default) for a request with no headers of interest.
     * @return void
     */
    public function __construct(
        private readonly string $method,
        string $uri,
        private readonly array $data = [],
        private readonly array $headers = [],
    ) {
        $this->path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $query = parse_url($uri, PHP_URL_QUERY);

        $queryParams = [];
        if (is_string($query)) {
            parse_str($query, $queryParams);
        }

        $this->queryParams = $queryParams;
    }

    /**
     * Builds a `Request` from PHP's own superglobals (`$_SERVER['REQUEST_METHOD']`, `['REQUEST_URI']`, and
     * `$_POST`), defaulting to `GET /` if the method/URI are missing. Headers come from every `$_SERVER` key
     * starting with `HTTP_` — `HTTP_ACCEPT_LANGUAGE` becomes `Accept-Language`, matching how `header()` expects
     * to be called.
     *
     * @return self A `Request` reflecting the current PHP superglobals.
     */
    public static function createFromGlobals(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        assert(is_string($uri));

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        assert(is_string($method));

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (!is_string($key) || !str_starts_with($key, 'HTTP_') || !is_string($value)) {
                continue;
            }

            $name = implode('-', array_map(
                callback: ucfirst(...),
                array: explode('_', strtolower(substr($key, 5))),
            ));

            $headers[$name] = $value;
        }

        return new self(strtoupper($method), $uri, $_POST, $headers);
    }

    /**
     * Returns the HTTP method this request was made with.
     *
     * @return string The HTTP method, e.g. `GET`, `POST`.
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Checks this request's HTTP method against `$type` — a small, deliberately limited version of CakePHP's own
     * `ServerRequest::is()`: only a method check for now (`'get'`/`'post'`), no detector registry, no other
     * request conditions (ajax, https, content type). Case doesn't matter — `is('post')` and `is('POST')` both
     * work, matching CakePHP's own lowercase convention in its examples even though `method()` itself always
     * returns the request's method uppercase.
     *
     * @param string $type The HTTP method to check for, e.g. `'get'`, `'post'`.
     * @return bool Whether the current request's method matches `$type`.
     */
    public function is(string $type): bool
    {
        return strtoupper($type) === $this->method;
    }

    /**
     * Restricts this request to one of `$methods` — the same idiom as CakePHP's own
     * `ServerRequest::allowMethod()`, called as the first line of an action that should only ever run for
     * specific HTTP methods:
     *
     * ```
     * public function submit(): Response
     * {
     *     $this->request->allowMethod('post');
     *     ...
     * }
     * ```
     *
     * @param list<string>|string $methods One or more HTTP methods this request must match, e.g. `'post'` or
     *  `['get', 'post']`. Case doesn't matter, same as `is()`.
     * @throws \Elone\Core\Exception\MethodNotAllowedException If the current request's method isn't among
     *  `$methods`.
     */
    public function allowMethod(array|string $methods): void
    {
        $methods = is_array($methods) ? $methods : [$methods];

        if (array_any($methods, fn($allowedMethod) => $this->is($allowedMethod))) {
            return;
        }

        throw new MethodNotAllowedException(sprintf(
            'Method `%s` is not allowed. Allowed: %s.',
            $this->method,
            implode(', ', array_map(
                callback: fn($allowedMethod) => '`' . strtoupper($allowedMethod) . '`',
                array: $methods,
            )),
        ));
    }

    /**
     * Returns the request's path, as parsed from the URI given to the constructor.
     *
     * @return string The request path, without the query string.
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * Returns every query string parameter parsed from the URI given to the constructor. Keys are usually strings, but
     * PHP coerces a purely numeric key (e.g. from `?123=abc`) to an integer, so the array key type is `array-key`
     * (`int|string`) rather than just `string`.
     *
     * @return array<array-key, mixed> The query string parameters, as an associative array.
     */
    public function queryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * Returns a single query string parameter by name.
     *
     * @param string $name The parameter name to look up.
     * @param mixed $default The value to return if `$name` isn't present.
     * @return mixed The parameter's value, or `$default`.
     */
    public function queryParam(string $name, mixed $default = null): mixed
    {
        return $this->queryParams[$name] ?? $default;
    }

    /**
     * Returns every field in the request body — a POST form submission, most commonly. Populated from PHP's own
     * `$_POST` when built via `createFromGlobals()`.
     *
     * @return array<array-key, mixed> The request body fields, as an associative array.
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Returns a single request body field by name.
     *
     * @param string $name The field name to look up.
     * @param mixed $default The value to return if `$name` isn't present.
     * @return mixed The field's value, or `$default`.
     */
    public function dataParam(string $name, mixed $default = null): mixed
    {
        return $this->data[$name] ?? $default;
    }

    /**
     * Returns every request header. Populated from `$_SERVER`'s own `HTTP_*` keys when built via
     * `createFromGlobals()`, normalized to standard header casing (`Accept-Language`, not `HTTP_ACCEPT_LANGUAGE`).
     *
     * @return array<string, string> The request headers, keyed by their standard name.
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Returns a single request header by name.
     *
     * @param string $name The header name to look up, e.g. `Accept-Language`.
     * @param string|null $default The value to return if `$name` isn't present.
     * @return string|null The header's value, or `$default`.
     */
    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[$name] ?? $default;
    }
}
