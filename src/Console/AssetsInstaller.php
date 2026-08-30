<?php
declare(strict_types=1);

namespace App\Console;

use Composer\Script\Event;

/**
 * Prepares the assets webroot needs to run: vendor CSS/JS copied into place, and each story's image folder made
 * available under `webroot/assets/img/stories/`. Registered as a Composer script, run automatically after
 * `composer install`/`composer update`.
 *
 * @codeCoverageIgnore
 */
final class AssetsInstaller
{
    public static function install(Event $event): void
    {
        $root = dirname(__DIR__, 2);

        self::ensureDirectories($root);
        self::copyBootstrap($root);
        self::linkStoryImages($root, $event);
    }

    private static function ensureDirectories(string $root): void
    {
        foreach (['css', 'js', 'img/stories'] as $path) {
            $directory = "$root/webroot/assets/$path";

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }
        }
    }

    private static function copyBootstrap(string $root): void
    {
        copy(
            "$root/vendor/twbs/bootstrap/dist/css/bootstrap.min.css",
            "$root/webroot/assets/css/bootstrap.min.css",
        );

        copy(
            "$root/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js",
            "$root/webroot/assets/js/bootstrap.bundle.min.js",
        );
    }

    private static function linkStoryImages(string $root, Event $event): void
    {
        $sources = glob("$root/resources/stories/*/img", GLOB_ONLYDIR) ?: [];

        foreach ($sources as $source) {
            $storyId = basename(dirname($source));
            $target = "$root/webroot/assets/img/stories/$storyId";

            if (file_exists($target)) {
                continue;
            }

            $realSource = realpath($source);
            if ($realSource === false) {
                continue;
            }

            if (PHP_OS_FAMILY === 'Windows') {
                self::junction($realSource, $target, $event);
            } else {
                symlink($realSource, $target);
            }
        }
    }

    private static function junction(string $source, string $target, Event $event): void
    {
        exec(sprintf('mklink /J %s %s', escapeshellarg($target), escapeshellarg($source)), $output, $resultCode);

        if ($resultCode !== 0) {
            $event->getIO()->writeError("Failed to create junction for `$source`.");
        }
    }
}
