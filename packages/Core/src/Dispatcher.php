<?php
declare(strict_types=1);

namespace Elone\Core;

use Elone\Core\Exception\HttpException;
use Elone\Core\Exception\UnsupportedParameterTypeException;
use Elone\Core\Routing\ControllerName;
use Elone\Core\Routing\Route;
use Elone\Core\Server\Response;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

readonly class Dispatcher
{
    public function __construct(private Configuration $configuration)
    {
    }

    /**
     * @param \Elone\Core\Routing\Route $route
     * @return \Elone\Core\Server\Response
     */
    public function dispatch(Route $route): Response
    {
        $controllerClass = $route->controllerClass();

        $method = new ReflectionMethod($controllerClass, $route->action);

        $controller = new $controllerClass($this->configuration);

        $arguments = self::resolveArguments($method, $route->params);

        $result = $method->invokeArgs($controller, $arguments);

        if ($result instanceof Response) {
            return $result;
        }

        $template = $this->templateName($route);
        $content = $controller->render($template);

        return new Response($content);
    }

    /**
     * @param list<string> $params
     * @return list<mixed>
     */
    protected static function resolveArguments(ReflectionMethod $method, array $params): array
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
     * @throws \Elone\Core\Exception\UnsupportedParameterTypeException If the parameter's built-in type is not one this
     * method knows how to build from a URL segment.
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
                "Invalid integer parameter '$value' for `\${$parameter->getName()}`.",
            ),

            'float' => filter_var(
                $value,
                FILTER_VALIDATE_FLOAT,
                FILTER_NULL_ON_FAILURE,
            ) ?? throw new HttpException(
                "Invalid float parameter '$value' for `\${$parameter->getName()}`.",
            ),

            'bool' => match (strtolower($value)) {
                '1', 'true', 'yes' => true,
                '0', 'false', 'no' => false,
                default => throw new HttpException(
                    "Invalid boolean parameter '$value' for `\${$parameter->getName()}`.",
                ),
            },

            'string' => $value,

            default => throw new UnsupportedParameterTypeException(
                "Unsupported parameter type '{$type->getName()}' for `\${$parameter->getName()}`.",
            ),
        };
    }

    protected function templateName(Route $route): string
    {
        return new ControllerName($route->controller)->kebabCase() . "/$route->action";
    }
}
