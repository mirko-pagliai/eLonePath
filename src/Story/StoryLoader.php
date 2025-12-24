<?php
declare(strict_types=1);

namespace eLonePath\Story;

use InvalidArgumentException;
use RuntimeException;

/**
 * Story loader.
 *
 * Loads and validates story files from JSON.
 */
class StoryLoader
{
    /**
     * Directory where story files are stored.
     */
    protected string $storiesDirectory;

    /**
     * Constructor for initializing the stories' directory.
     *
     * @param string $storiesDirectory Path to the directory containing story files. Defaults to the 'resources/stories' directory.
     * @return void
     * @throws \InvalidArgumentException If the provided path does not exist or is not a directory.
     */
    public function __construct(string $storiesDirectory = '')
    {
        if (!$storiesDirectory) {
            $storiesDirectory = RESOURCES . DS . 'stories';
        }

        if (!file_exists($storiesDirectory)) {
            throw new InvalidArgumentException("File or directory `{$storiesDirectory}` does not exist.");
        }
        if (!is_dir($storiesDirectory)) {
            throw new InvalidArgumentException("File or directory `{$storiesDirectory}` is not a directory.");
        }

        $this->storiesDirectory = rtrim($storiesDirectory, DS);
    }

    /**
     * Loads a story file, validates its content, and returns a Story object.
     *
     * @param string $filename The name of the story file to load.
     * @return \eLonePath\Story\Story The loaded and validated Story object.
     * @throws \RuntimeException If the file does not exist, is not readable, or contains invalid JSON.
     * @throws \InvalidArgumentException If the story validation fails.
     */
    public function load(string $filename): Story
    {
        $filepath = $this->storiesDirectory . DS . $filename;

        if (!file_exists($filepath) || !is_readable($filepath)) {
            throw new RuntimeException("File or directory `{$filepath}` does not exist or is not readable.");
        }

        $data = json_decode(file_get_contents($filepath) ?: '', true);
        if ($data === null) {
            throw new RuntimeException("Failed to parse JSON in `{$filepath}` story file");
        }

        /** @phpstan-ignore argument.type */
        $story = Story::fromArray($data);

        $validationErrors = $story->validate();
        if (!empty($validationErrors)) {
            throw new InvalidArgumentException(
                "Story validation failed in `{$filepath}`:\n- " . implode("\n- ", $validationErrors)
            );
        }

        return $story;
    }

    /**
     * List all available story files.
     *
     * @return array<string> List of story filenames
     */
    public function listAvailableStories(): array
    {
        $files = glob($this->storiesDirectory . DS . '*.json');
        if ($files === false) {
            return [];
        }

        return array_map(fn(string $path): string => basename($path), $files);
    }

    /**
     * Check if a story file exists.
     *
     * @param string $filename Story filename
     * @return bool
     */
    public function exists(string $filename): bool
    {
        $filepath = $this->storiesDirectory . DS . $filename;

        return file_exists($filepath);
    }
}
