<?php
declare(strict_types=1);

namespace App\Core\Routing;

use App\Core\Exception\RouteNotFoundException;

final readonly class Route
{
    /**
     * @param list<string> $params
     */
    public function __construct(public string $controller, public string $action, public array $params = [])
    {
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $controller)) {
            throw new RouteNotFoundException("Invalid controller name: `$controller`.");
        }
    }

    /**
     * @return class-string<\App\Core\Controller>
     */
    public function controllerClass(): string
    {
        $name = str_replace(
            ' ',
            '',
            ucwords(str_replace(['-', '_'], ' ', $this->controller)),
        );

        /** @var class-string<\App\Core\Controller> $className */
        $className = "App\\Controller\\{$name}Controller";

        return $className;
    }

    public function path(): string
    {
        $segments = [
            strtolower($this->controller),
            $this->action,
            ...$this->params,
        ];

        return '/' . implode('/', array_map(
            callback: static fn(string $value): string => rawurlencode($value),
            array: $segments,
        ));
    }
}
