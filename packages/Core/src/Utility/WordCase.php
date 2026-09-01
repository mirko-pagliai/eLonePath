<?php
declare(strict_types=1);

namespace Elone\Core\Utility;

/**
 * Splits a raw string into words (on `-`/`_`, or PascalCase/camelCase boundaries) and converts them into
 * different case formats. Used by the routing layer to move between a URL segment, a controller class name, and
 * a template path — but the conversion itself has nothing to do with controllers specifically.
 */
final readonly class WordCase
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
     * studlyCase, e.g. `UsersSettings` — used for class names or variable naming conventions. A word that is already
     * entirely uppercase (an acronym, e.g. `API`) is kept as-is instead of being re-cased.
     *
     * @return string Returns the converted string in StudlyCase format.
     */
    public function studlyCase(): string
    {
        return implode('', array_map(
            static fn(string $word): string => ctype_upper($word) ? $word : ucfirst(strtolower($word)),
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
     * Splits a raw string into an array of words based on delimiters (dashes, underscores) or PascalCase/camelCase
     * boundaries. A run of consecutive uppercase letters is kept together as a single word (e.g. `API`, `HTML`)
     * unless it's immediately followed by a lowercase letter, in which case the last uppercase letter of the run
     * starts the next word instead (e.g. `HTMLParser` splits into `HTML` and `Parser`).
     *
     * @param string $raw The input string to be split into words.
     * @return list<string> An array of words obtained from the input string.
     */
    private static function split(string $raw): array
    {
        $words = [];
        $current = '';
        $length = strlen($raw);

        for ($i = 0; $i < $length; $i++) {
            $char = $raw[$i];

            if ($char === '-' || $char === '_') {
                if ($current !== '') {
                    $words[] = $current;
                    $current = '';
                }

                continue;
            }

            $next = $i + 1 < $length ? $raw[$i + 1] : '';
            $previousWasUpper = $current !== '' && ctype_upper($current[-1]);

            if (ctype_upper($char) && $current !== '' && (!$previousWasUpper || ctype_lower($next))) {
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
