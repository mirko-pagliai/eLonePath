<?php
declare(strict_types=1);

namespace Elone\Core;

use Elone\Core\Exception\HttpException;
use Elone\Core\Routing\Route;
use Elone\Core\Server\Response;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

final readonly class Dispatcher
{
    /**
     * @param \Elone\Core\Routing\Route $route
     * @return \Elone\Core\Server\Response
     */
    public function dispatch(Route $route): Response
    {
        $controllerClass = $route->controllerClass();

        $method = new ReflectionMethod($route->controllerClass(), $route->action);

        $controller = new $controllerClass();

        $arguments = self::resolveArguments($method, $route->params);

        $result = $method->invokeArgs($controller, $arguments);

        if ($result instanceof Response) {
            return $result;
        }

        $template = $this->templateName($controllerClass, $route->action);
        $content = $controller->render($template);

        return new Response($content);
    }

    /**
     * @param list<string> $params
     * @return list<mixed>
     */
    public static function resolveArguments(ReflectionMethod $method, array $params): array
    {
        $parameters = $method->getParameters();

        if (count($params) !== count($parameters)) {
            throw new HttpException(sprintf(
                'Invalid number of parameters for %s::%s(). Expected %d, received %d.',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
                count($parameters),
                count($params),
            ));
        }

        $arguments = [];

        foreach ($parameters as $index => $parameter) {
            $arguments[] = self::convert(
                $params[$index],
                $parameter,
            );
        }

        return $arguments;
    }

    /**
     * Converts a string value to a specified type based on the provided parameter's type hint.
     *
     * @param string $value The string value to be converted.
     * @param \ReflectionParameter $parameter The reflection parameter containing type information for the conversion.
     * @return mixed The converted value, matching the parameter's type hint.
     * @throws \Elone\Core\Exception\HttpException If the conversion fails due to an invalid value matching a built-in type.
     */
    private static function convert(string $value, ReflectionParameter $parameter): mixed
    {
        $type = $parameter->getType();
        if (!$type instanceof ReflectionNamedType || !$type->isBuiltin()) {
            return $value;
        }

        return match ($type->getName()) {
            'int' => filter_var(
                $value,
                FILTER_VALIDATE_INT,
                FILTER_NULL_ON_FAILURE,
            ) ?? throw new HttpException(
                "Invalid integer parameter '{$value}' for \${$parameter->getName()}.",
            ),

            'float' => filter_var(
                $value,
                FILTER_VALIDATE_FLOAT,
                FILTER_NULL_ON_FAILURE,
            ) ?? throw new HttpException(
                "Invalid float parameter '{$value}' for \${$parameter->getName()}.",
            ),

            'bool' => match (strtolower($value)) {
                '1', 'true', 'yes' => true,
                '0', 'false', 'no' => false,
                default => throw new HttpException(
                    "Invalid boolean parameter '{$value}' for \${$parameter->getName()}.",
                ),
            },

            'string' => $value,

            default => $value,
        };
    }

    private function templateName(string $controllerClass, string $action): string
    {
        $shortName = basename(str_replace('\\', '/', $controllerClass));

        $controller = substr($shortName, 0, -10);

        return strtolower($controller) . '/' . $action;
    }
}
