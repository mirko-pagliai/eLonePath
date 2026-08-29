<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exception\HttpException;
use App\Core\Server\Response;
use App\Core\View\View;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;

final readonly class Dispatcher
{
    public function __construct(private View $view)
    {
    }

    /**
     * It receives the router result.
     *
     * It first checks that:
     *
     * - the class exists and extends `Controller`;
     * - the method exists and is public.
     *
     * It then instantiates the class with the view, resolves the arguments, and executes the requested method.
     *
     * If the method returns a response, it returns that. Otherwise, it automatically proceeds with rendering.
     *
     * @param list<string> $params
     */
    public function dispatch(string $controllerClass, string $action, array $params): Response
    {
        if (!class_exists($controllerClass)) {
            throw new HttpException("Controller not found: $controllerClass.");
        }

        if (!is_subclass_of($controllerClass, Controller::class)) {
            throw new RuntimeException("$controllerClass must extend Controller.");
        }

        if (!method_exists($controllerClass, $action)) {
            throw new HttpException("Action not found: $controllerClass::$action().");
        }

        $controller = new $controllerClass($this->view);

        $method = new ReflectionMethod($controller, $action);
        if (!$method->isPublic()) {
            throw new HttpException("Action is not public: $controllerClass::$action().");
        }

        $arguments = $this->resolveArguments($method, $params);

        $result = $method->invokeArgs($controller, $arguments);

        if ($result instanceof Response) {
            return $result;
        }

        $content = $this->view->render($this->templateName($controllerClass, $action));

        return new Response($content);
    }

    /**
     * @param list<string> $params
     * @return list<mixed>
     */
    private function resolveArguments(ReflectionMethod $method, array $params): array
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
            $arguments[] = $this->convert(
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
     * @throws \App\Core\Exception\HttpException If the conversion fails due to an invalid value matching a built-in type.
     */
    private function convert(string $value, ReflectionParameter $parameter): mixed
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
