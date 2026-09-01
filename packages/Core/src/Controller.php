<?php
declare(strict_types=1);

namespace Elone\Core;

use Elone\Core\Server\Request;
use Elone\Core\View\View;

/**
 * Represents the base controller class responsible for managing the interaction between the view and the data provided.
 */
abstract class Controller
{
    protected readonly Request $request;

    protected readonly View $view;

    /**
     * Initializes a new instance of the class.
     *
     * @param \Elone\Core\Server\Request|null $request An optional `Request` instance. If not provided, the current
     * request is captured from PHP's own superglobals.
     * @param \Elone\Core\View\View|null $view An optional `View` instance. If not provided, a new instance will be
     * created using the configuration.
     * @return void
     */
    public function __construct(?Request $request = null, ?View $view = null)
    {
        $this->request = $request ?? Request::capture();
        $this->view = $view ?? new View();
    }

    /**
     * Renders the specified template with an optional layout.
     *
     * @param string $template The name of the template to be rendered.
     * @param string|null $layout The name of the layout to apply. Defaults to 'default'.
     * @return string The rendered output as a string.
     */
    public function render(string $template, ?string $layout = 'default'): string
    {
        return $this->view->render(template: $template, layout: $layout);
    }

    /**
     * Sets the provided data into the view.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    protected function set(array $data): self
    {
        $this->view->set(data: $data);

        return $this;
    }

    /**
     * Retrieves all query string parameters of the current request.
     *
     * @return array<string, mixed>
     */
    protected function queryParams(): array
    {
        return $this->request->queryParams();
    }

    /**
     * Retrieves a single query string parameter of the current request, or `$default` if it isn't present.
     */
    protected function queryParam(string $name, mixed $default = null): mixed
    {
        return $this->request->queryParam(name: $name, default: $default);
    }
}
