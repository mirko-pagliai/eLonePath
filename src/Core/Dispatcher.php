<?php
declare(strict_types=1);

namespace App\Core;

use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;

final readonly class Dispatcher
{
    public function __construct(private View $view)
    {
    }

    public function dispatch(string $controllerClass, string $action, array $params): Response
    {
        if (!class_exists($controllerClass)) {
            throw new HttpException(400, "Controller not found: {$controllerClass}");
        }

        if (!is_subclass_of($controllerClass, Controller::class)) {
            throw new RuntimeException("{$controllerClass} must extend Controller.");
        }

        if (!method_exists($controllerClass, $action)) {
            throw new HttpException(400, "Action not found: {$controllerClass}::{$action}()");
        }

        $controller = new $controllerClass($this->view);

        $method = new ReflectionMethod($controller, $action);

        if (!$method->isPublic()) {
            throw new HttpException(400, "Action is not public: {$controllerClass}::{$action}()");
        }

        $arguments = $this->resolveArguments($method, $params);

        $result = $method->invokeArgs($controller, $arguments);

        if ($result instanceof Response) {
            return $result;
        }

        $content = $this->view->render($this->templateName($controllerClass, $action));

        return new Response($content);
    }

    private function resolveArguments(ReflectionMethod $method, array $params): array
    {
        $parameters = $method->getParameters();

        if (count($params) !== count($parameters)) {
            throw new HttpException(400, sprintf(
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

    private function convert(string $value, ReflectionParameter $parameter): mixed
    {
        $type = $parameter->getType();

        if ($type === null || $type->isBuiltin() === false) {
            return $value;
        }

        return match ($type->getName()) {
            'int' => filter_var(
                $value,
                FILTER_VALIDATE_INT,
                FILTER_NULL_ON_FAILURE,
            ) ?? throw new HttpException(
                400,
                "Invalid integer parameter '{$value}' for \${$parameter->getName()}.",
            ),

            'float' => filter_var(
                $value,
                FILTER_VALIDATE_FLOAT,
                FILTER_NULL_ON_FAILURE,
            ) ?? throw new HttpException(
                400,
                "Invalid float parameter '{$value}' for \${$parameter->getName()}.",
            ),

            'bool' => match (strtolower($value)) {
                '1', 'true', 'yes' => true,
                '0', 'false', 'no' => false,
                default => throw new HttpException(
                    400,
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

        $controller = preg_replace(
            '/Controller$/',
            '',
            $shortName,
        );

        return strtolower($controller) . '/' . $action;
    }
}
