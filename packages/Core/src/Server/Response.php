<?php
declare(strict_types=1);

namespace Elone\Core\Server;

/**
 * An HTTP response: a status code, a body, and, optionally, headers — most commonly `Location`, for redirects
 * built via `Controller::redirect()`.
 */
final readonly class Response
{
    /**
     * @param string $content The response body.
     * @param int $status The HTTP status code.
     * @param array<string, string> $headers Header name/value pairs, sent as-is by `send()`.
     * @return void
     */
    public function __construct(private string $content = '', private int $status = 200, private array $headers = [])
    {
    }

    /**
     * @return string The response body.
     */
    public function content(): string
    {
        return $this->content;
    }

    /**
     * @return int The HTTP status code.
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return array<string, string> Header name/value pairs.
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Sends the status code and headers, then echoes the body.
     *
     * @return void
     */
    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->content;
    }
}
