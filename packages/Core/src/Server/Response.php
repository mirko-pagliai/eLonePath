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
     * @param array<string, string> $headers
     */
    public function __construct(private string $content = '', private int $status = 200, private array $headers = [])
    {
    }

    public function content(): string
    {
        return $this->content;
    }

    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->content;
    }
}
