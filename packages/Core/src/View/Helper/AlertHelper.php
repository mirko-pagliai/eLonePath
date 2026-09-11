<?php
declare(strict_types=1);

namespace Elone\Core\View\Helper;

use InvalidArgumentException;

/**
 * Builds a Bootstrap alert — the eight basic color variants from
 * https://getbootstrap.com/docs/5.3/components/alerts/#examples, nothing more elaborate (no dismiss button, no
 * icon, no `alert-link` styling).
 */
final class AlertHelper extends Helper
{
    /**
     * @param 'primary'|'secondary'|'success'|'danger'|'warning'|'info'|'light'|'dark' $variant One of Bootstrap's
     *  eight basic alert color variants.
     * @param string $message The alert's text content — HTML-escaped.
     * @param array<string, string|int|float|bool> $options Extra HTML attributes for the `<div>`; `class` is
     *  merged alongside the alert's own two classes rather than overwritten, the same way `HtmlHelper::icon()`
     *  merges an extra `class` rather than replacing its own.
     * @return string The rendered `<div class="alert alert-{$variant}" role="alert">...</div>`.
     * @throws \InvalidArgumentException If `$variant` isn't one of the eight known ones.
     */
    public function render(string $variant, string $message, array $options = []): string
    {
        if (
            !in_array(
                needle: $variant,
                haystack: ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'],
                strict: true,
            )
        ) {
            throw new InvalidArgumentException("Unknown alert variant: `$variant`.");
        }

        $class = "alert alert-$variant";
        if (isset($options['class'])) {
            $class .= ' ' . $options['class'];
            unset($options['class']);
        }

        $htmlAttributes = $this->parseHtmlAttributes($options);

        return sprintf(
            '<div class="%s" role="alert"%s>%s</div>',
            h($class, ENT_QUOTES),
            $htmlAttributes,
            h($message),
        );
    }
}
