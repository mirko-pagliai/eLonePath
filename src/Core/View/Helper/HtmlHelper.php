<?php
declare(strict_types=1);

namespace App\Core\View\Helper;

final class HtmlHelper
{
    public function url(array $route): string
    {
        return '/' . implode(
            '/',
            array_map(
                static fn(mixed $value): string => rawurlencode((string) $value),
                $route,
            ),
        );
    }

    public function link(string $text, array $route,array $attributes = []): string
    {
        $htmlAttributes = '';

        foreach ($attributes as $name => $value) {
            $htmlAttributes .= sprintf(
                ' %s="%s"',
                htmlspecialchars((string) $name, ENT_QUOTES),
                htmlspecialchars((string) $value, ENT_QUOTES),
            );
        }

        return sprintf(
            '<a href="%s"%s>%s</a>',
            htmlspecialchars($this->url($route), ENT_QUOTES),
            $htmlAttributes,
            htmlspecialchars($text),
        );
    }
}
