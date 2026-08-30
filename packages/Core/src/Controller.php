<?php
declare(strict_types=1);

namespace Elone\Core;

use Elone\Core\View\View;

/**
 * Represents the base controller class responsible for managing the interaction between the view and the data provided.
 */
abstract class Controller
{
    protected readonly Configuration $configuration;

    protected readonly View $view;

    /**
     * Initializes a new instance of the class.
     *
     * @param \Elone\Core\Configuration $configuration The configuration instance required for initialization.
     * @param \Elone\Core\View\View|null $view An optional `View` instance. If not provided, a new instance will be
     * created using the configuration.
     * @return void
     */
    public function __construct(Configuration $configuration, ?View $view = null)
    {
        $this->configuration = $configuration;
        $this->view = $view ?? new View($configuration);
    }

    /**
     * Gets the `Configuration` instance.
     *
     * @return \Elone\Core\Configuration The current Configuration instance.
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
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
}
