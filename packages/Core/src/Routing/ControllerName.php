<?php
declare(strict_types=1);

namespace Elone\Core\Routing;

/**
 * Responsible for processing and converting raw string inputs into various case formats (e.g., PascalCase, kebab-case)
 * suitable for controller class naming and URL or template path generation.
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
     * studlyCase, e.g. `UsersSettings` — used for class names or variable naming conventions.
     *
     * @return string Returns the converted string in StudlyCase format.
     */
    public function studlyCase(): string
    {
        return implode('', array_map(
            static fn(string $word): string => ucfirst(strtolower($word)),
            $this->words,
        ));
    }

    /**
     * Converts the elements of an array into a kebab-case formatted string.
     *
     * @return string The kebab-case formatted string created by joining array elements with hyphens.
     */
    public function kebabCase(): string
    {
        return implode('-', array_map(strtolower(...), $this->words));
    }

    /**
     * Splits a raw string into an array of substrings based on delimiters such as dashes, underscores, or uppercase
     * letter transitions.
     *
     * @param string $raw The input string to be split into substrings.
     * @return list<string> An array of substrings obtained from the input string.
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
