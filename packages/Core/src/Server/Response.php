<?php
declare(strict_types=1);

namespace Elone\Core\Server;

final readonly class Response
{
    public function __construct(private string $content = '', private int $status = 200)
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

    public function send(): void
    {
        http_response_code($this->status);

        echo $this->content;
    }
}
