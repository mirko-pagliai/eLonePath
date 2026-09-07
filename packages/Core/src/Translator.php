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

        $file = LOCALES . "$locale/default.po";
        if (!is_file($file)) {
            return;
        }

        self::$translator->addLoader(format: 'po', loader: new PoFileLoader());
        self::$translator->addResource(
            format: 'po',
            resource: LOCALES . "$locale/default.po",
            locale: $locale,
            domain: 'default',
        );
    }

    /**
     * Translates the given string within the specified domain, replacing placeholders with the provided arguments.
     *
     * @param string $domain The domain to use for translation.
     * @param string $string The string to be translated.
     * @param string|int ...$args The arguments to replace placeholders in the string.
     * @return string The translated string with placeholders replaced by the provided arguments.
     */
    public static function translate(string $domain, string $string, string|int ...$args): string
    {
        if (!isset(self::$translator)) {
            self::init('en');
        }

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
