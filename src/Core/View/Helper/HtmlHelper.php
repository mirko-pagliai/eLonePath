<?php
declare(strict_types=1);

namespace App\Core\View\Helper;

use App\Core\Dispatcher;
use App\Core\Router;
use InvalidArgumentException;

final class HtmlHelper
{
    public function __construct(private readonly Router $router)
    {
    }

    public function url(array $route): string
    {
        $controller = $route[0] ?? null;
        $action = $route[1] ?? 'index';
        $params = array_slice($route, 2);

        if (!is_string($controller) || !is_string($action)) {
            throw new InvalidArgumentException('Invalid route.');
        }

        $resolved = $this->router->resolve(
            $controller,
            $action,
            array_map(
                callback: static fn(mixed $value): string => (string)$value,
                array: $params,
            ),
        );

        $method = Dispatcher::resolve($resolved['controller'], $resolved['action']);

        Dispatcher::resolveArguments($method, $resolved['params']);

        $segments = [
            $controller,
            $action,
            ...$resolved['params'],
        ];

        return '/' . implode('/', array_map(
            callback: static fn(string $value): string => rawurlencode($value),
            array: $segments,
        ));
    }

    public function link(string $text, array $route, array $attributes = []): string
    {
        $htmlAttributes = '';

        foreach ($attributes as $name => $value) {
            $htmlAttributes .= sprintf(
                ' %s="%s"',
                htmlspecialchars((string)$name, ENT_QUOTES),
                htmlspecialchars((string)$value, ENT_QUOTES),
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