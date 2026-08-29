<?php
declare(strict_types=1);

namespace App\Core\Server;

final class Request
{
    public function __construct(private readonly string $method, private readonly string $path)
    {
    }

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
