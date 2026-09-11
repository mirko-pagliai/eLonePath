<?php
declare(strict_types=1);

namespace Elone\Core;

use Gettext\Loader\PoLoader;
use Gettext\Translations;
use MessageFormatter;
use RuntimeException;

/**
 * Handles language translation functionality for the application.
 *
 * Loads `.po` files via `gettext/gettext`, and resolves ICU message patterns (placeholders, plurals) via PHP's
 * own `MessageFormatter`.
 */
final class Translator
{
    /**
     * @var array<string, \Gettext\Translations>
     */
    private static array $catalogues = [];

    private static string $locale = 'en';

    private static bool $initialized = false;

    /**
     * Initializes the translator with the specified locale and loads translation resources.
     *
     * @param string $locale The locale to be used for the translator.
     * @return void
     */
    public static function init(string $locale): void
    {
        self::$locale = $locale;
        self::$catalogues = [];
        self::$initialized = true;

        $loader = new PoLoader();
        $localeDir = rtrim(LOCALES, '/') . "/$locale";

        foreach (glob("$localeDir/*.po") ?: [] as $file) {
            $domain = basename($file, '.po');

            self::$catalogues[$domain] = $loader->loadFile($file, Translations::create($domain));
        }
    }

    /**
     * Translates the given string within the specified domain, replacing placeholders with the provided arguments.
     *
     * @param string $domain The domain to use for translation.
     * @param string $string The string to be translated.
     * @param string|int ...$args The arguments to replace placeholders in the string.
     * @return string The translated string with placeholders replaced by the provided arguments.
     * @throws \RuntimeException If the resolved pattern is not a valid ICU message.
     */
    public static function translate(string $domain, string $string, string|int ...$args): string
    {
        if (!self::$initialized) {
            self::init('en');
        }

        $pattern = self::lookup($domain, $string);

        $result = new MessageFormatter(self::$locale, $pattern)->format(array_values($args));
        if ($result === false) {
            throw new RuntimeException("Failed to format the message pattern `$pattern`.");
        }

        return $result;
    }

    /**
     * Finds `$string`'s own translation within `$domain`, falling back to `$string` itself when it isn't found
     * or hasn't been translated yet.
     *
     * @param string $domain The domain to look `$string` up in.
     * @param string $string The string to look up.
     * @return string The translated pattern, or `$string` itself.
     */
    private static function lookup(string $domain, string $string): string
    {
        $translation = (self::$catalogues[$domain] ?? null)?->find(null, $string);

        if ($translation === null || !$translation->isTranslated()) {
            return $string;
        }

        return $translation->getTranslation() ?? $string;
    }
}
