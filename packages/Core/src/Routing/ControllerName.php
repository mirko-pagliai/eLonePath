<?php
declare(strict_types=1);

namespace Elone\Core\Routing;

/**
 * Splits a raw controller identifier — as it appears in a URL segment (kebab-case, snake_case) or as written in
 * code (PascalCase) — into words, and exposes the casings the rest of the framework needs.
 */
final readonly class ControllerName
{
    /**
     * @var list<string>
     */
    private array $words;

    public function __construct(string $raw)
    {
        $this->words = self::split($raw);
    }

    /**
     * PascalCase, e.g. `UsersSettings` — used to build the controller's class name.
     */
    public function studlyCase(): string
    {
        return implode('', array_map(
            static fn(string $word): string => ucfirst(strtolower($word)),
            $this->words,
        ));
    }

    /**
     * kebab-case, e.g. `users-settings` — used to build URLs and template paths.
     */
    public function kebabCase(): string
    {
        return implode('-', array_map(strtolower(...), $this->words));
    }

    /**
     * @return list<string>
     */
    private static function split(string $raw): array
    {
        $words = [];
        $current = '';

        foreach (str_split($raw) as $char) {
            if ($char === '-' || $char === '_') {
                if ($current !== '') {
                    $words[] = $current;
                    $current = '';
                }

                continue;
            }

            if (ctype_upper($char) && $current !== '') {
                $words[] = $current;
                $current = '';
            }

            $current .= $char;
        }

        if ($current !== '') {
            $words[] = $current;
        }

        return $words;
    }
}
