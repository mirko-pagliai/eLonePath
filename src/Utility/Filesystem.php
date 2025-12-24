<?php
declare(strict_types=1);

namespace eLonePath\Utility;

use RuntimeException;

/**
 * Provides utility methods for performing common filesystem-related operations.
 */
class Filesystem
{
    /**
     * Checks if the provided directory path is readable and is a valid directory.
     *
     * @param string $path The directory path to validate.
     * @return void
     * @throws \RuntimeException If the directory is not readable or is not a directory.
     */
    public static function directoryIsReadable(string $path): void
    {
        if (!is_readable($path)) {
            throw new RuntimeException("Directory `$path` is not readable.");
        }
        if (!is_dir($path)) {
            throw new RuntimeException("`$path` is not a directory.");
        }
    }

    /**
     * Checks if the provided file path is readable and is a valid file.
     *
     * @param string $path The file path to validate.
     * @return void
     * @throws \RuntimeException If the file is not readable or is not a file.
     */
    public static function fileIsReadable(string $path): void
    {
        if (!is_readable($path)) {
            throw new RuntimeException("File `$path` is not readable.");
        }
        if (!is_file($path)) {
            throw new RuntimeException("`$path` is not a file.");
        }
    }

    /**
     * Reads and parses JSON data from the specified file.
     *
     * @param string $path The file path of the JSON file to read.
     * @return array<array-key, mixed> The decoded JSON data as an associative array.
     * @throws \RuntimeException If the file is not readable, if the JSON data cannot be parsed, or if the parsed data
     *  is not an array or object.
     */
    public static function readJsonDataFromFile(string $path): array
    {
        self::fileIsReadable($path);

        $jsonData = json_decode(file_get_contents($path) ?: '', true);
        if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Failed to parse JSON data from `' . $path . '` file: "' . lcfirst(json_last_error_msg()) . '".');
        }
        if (!is_array($jsonData)) {
            throw new RuntimeException("JSON data in `$path` must be an object or array.");
        }

        return $jsonData;
    }
}