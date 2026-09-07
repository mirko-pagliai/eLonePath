<?php
declare(strict_types=1);

namespace Elone\Core;

use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Translator as SymfonyTranslator;

/**
 * Handles language translation functionality for the application.
 *
 * This class uses the Symfony Translator component for managing translations.
 *
 * It allows initialization with a specific locale and supports loading translation resources from PO files.
 */
final class Translator
{
    private static SymfonyTranslator $translator;

    /**
     * Initializes the translator with the specified locale and loads translation resources.
     *
     * @param string $locale The locale to be used for the translator.
     * @return void
     */
    public static function init(string $locale): void
    {
        self::$translator = new SymfonyTranslator(locale: $locale);
        self::$translator->addLoader(format: 'po', loader: new PoFileLoader());

        $files = glob(LOCALES . '/*/default.po') ?: [];
        foreach ($files as $file) {
            $language = basename(dirname($file));

            self::$translator->addResource(
                format: 'po',
                resource: $file,
                locale: $language,
                domain: 'default',
            );
        }
    }

    /**
     * Translates a string using the specified domain and optional parameters.
     *
     * @param string $domain The domain in which the translation should be looked up.
     * @param string $string The string to be translated.
     * @param string ...$args Optional arguments to replace placeholders within the string.
     * @return string The translated string with placeholders replaced by the provided arguments.
     */
    public static function translate(string $domain, string $string, string ...$args): string
    {
        $parameters = array_combine(
            keys: array_map(
                callback: fn(int $index): string => '{' . $index . '}',
                array: array_keys($args),
            ),
            values: array_values($args),
        );

        return self::$translator->trans(id: $string, parameters: $parameters, domain: $domain);
    }
}
