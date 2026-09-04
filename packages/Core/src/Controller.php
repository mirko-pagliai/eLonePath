<?php
declare(strict_types=1);

namespace Elone\Core;

use Elone\Core\Routing\Route;
use Elone\Core\Server\Request;
use Elone\Core\Server\Response;
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
     * request is built via `Request::createFromGlobals()`.
     * @param \Elone\Core\View\View|null $view An optional `View` instance. If not provided, a new instance of
     * `viewClass()` will be created.
     * @return void
     */
    public function __construct(?Request $request = null, ?View $view = null)
    {
        $this->request = $request ?? Request::createFromGlobals();

        if ($view !== null) {
            $this->view = $view;
        } else {
            $viewClass = static::viewClass();
            $this->view = new $viewClass();
        }
    }

    /**
     * The `View` class to instantiate when none is explicitly given to the constructor. Override this in a subclass —
     * the app's own `AppController`, typically — to use a `View` subclass that loads additional helpers; `Controller`
     * itself only knows the base `View`, not any app-specific one.
     *
     * @return class-string<\Elone\Core\View\View>
     */
    protected static function viewClass(): string
    {
        return View::class;
    }

    /**
     * Renders the specified template with an optional layout.
     *
     * @param string $template The name of the template to be rendered.
     * @param string|null $layout The name of the layout to apply. Defaults to 'default'.
     * @return string The rendered output as a string.
     * @throws \Elone\Core\Exception\TemplateNotFoundException If `$template` doesn't resolve to an existing file.
     * @throws \Elone\Core\Exception\LayoutNotFoundException If `$layout` doesn't resolve to an existing file.
     * @throws \Throwable Whatever the template (or layout) itself throws while rendering.
     */
    public function render(string $template, ?string $layout = 'default'): string
    {
        return $this->view->render(template: $template, layout: $layout);
    }

    /**
     * Sets the provided data into the view.
     *
     * @param array<string, mixed> $data The data to make available in the rendered template.
     * @return static
     */
    protected function set(array $data): static
    {
        $this->view->set(data: $data);

        return $this;
    }

    /**
     * Whether the current request's HTTP method is `$type` — see `Request::is()`.
     *
     * @param string $type The HTTP method to check for, e.g. `'get'`, `'post'`.
     */
    protected function is(string $type): bool
    {
        return $this->request->is($type);
    }

    /**
     * Restricts the current action to one of `$methods` — see `Request::allowMethod()`. Call as the first line of
     * an action that should only run for specific HTTP methods:
     *
     * ```
     * public function submit(): Response
     * {
     *     $this->allowMethod('post');
     *     ...
     * }
     * ```
     *
     * @param list<string>|string $methods One or more HTTP methods this request must match.
     * @throws \Elone\Core\Exception\MethodNotAllowedException If the current request's method isn't among
     *  `$methods`.
     */
    protected function allowMethod(array|string $methods): void
    {
        $this->request->allowMethod($methods);
    }

    /**
     * Retrieves all query string parameters of the current request.
     *
     * @return array<array-key, mixed> The query string parameters, as an associative array.
     */
    protected function queryParams(): array
    {
        return $this->request->queryParams();
    }

    /**
     * Retrieves a single query string parameter of the current request, or `$default` if it isn't present.
     *
     * @param string $name The parameter name to look up.
     * @param mixed $default The value to return if `$name` isn't present.
     * @return mixed The parameter's value, or `$default`.
     */
    protected function queryParam(string $name, mixed $default = null): mixed
    {
        return $this->request->queryParam($name, $default);
    }

    /**
     * Builds a redirect `Response` to `$url`: a string is used as-is (a literal path such as `/`, or an external URL),
     * an array is built into a route.
     *
     * Returns it directly from an action to have `Dispatcher` send it as-is, bypassing the view entirely:
     *
     * ```
     * return $this->redirect('/');
     * return $this->redirect(['controller' => 'Pages', 'action' => 'home']);
     * ```
     *
     * @param array<string|int, string|int|float|bool>|string $url A literal URL/path, or a route array.
     * @param int $status The HTTP status code for the redirect. Defaults to `302` (temporary).
     * @return \Elone\Core\Server\Response
     * @throws \Elone\Core\Exception\RouteNotFoundException If given an array route with invalid or missing parameters.
     */
    protected function redirect(array|string $url, int $status = 302): Response
    {
        return new Response(status: $status, headers: ['Location' => Route::resolve($url)]);
    }
}
