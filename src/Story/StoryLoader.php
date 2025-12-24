<?php
declare(strict_types=1);

namespace eLonePath\Story;

use eLonePath\Utility\Filesystem;
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
     * @throws \RuntimeException If the provided path does not exist or is not a directory.
     */
    public function __construct(string $storiesDirectory = '')
    {
        $this->storiesDirectory = rtrim($storiesDirectory ?: RESOURCES . DS . 'stories', DS);
        Filesystem::directoryIsReadable($this->storiesDirectory);
    }

    /**
     * Loads a story file, validates its content, and returns a Story object.
     *
     * @param string $storyId Story ID (directory name)
     * @return \eLonePath\Story\Story The loaded and validated Story object.
     * @throws \RuntimeException If the directory or files do not exist, are not readable, or contain invalid JSON.
     * @throws \InvalidArgumentException If the story validation fails.
     */
    public function load(string $storyId): Story
    {
        $storyPath = $this->storiesDirectory . DS . $storyId;

        Filesystem::directoryIsReadable($storyPath);
        $metadata = Filesystem::readJsonDataFromFile($storyPath . DS . 'metadata.json');
        $content = Filesystem::readJsonDataFromFile($storyPath . DS . 'en.json');

        $data = [
            'metadata' => $metadata,
            'paragraphs' => $content['paragraphs'] ?? [],
        ];

        /** @phpstan-ignore argument.type */
        $story = Story::fromArray($data);

        $validationErrors = $story->validate();
        if (!empty($validationErrors)) {
            throw new InvalidArgumentException(
                "Story validation failed in `{$storyPath}`:\n- " . implode("\n- ", $validationErrors)
            );
        }

        return $story;
    }

    /**
     * List all available story files.
     *
     * @return array<string> List of story IDs
     */
    public function listAvailableStories(): array
    {
        $directories = glob($this->storiesDirectory . DS . '*', GLOB_ONLYDIR);
        if ($directories === false) {
            return [];
        }

        return array_map(fn(string $path): string => basename($path), $directories);
    }

    /**
     * Check if a story file exists.
     *
     * @param string $storyId Story ID
     * @return bool
     */
    public function exists(string $storyId): bool
    {
        $storyPath = $this->storiesDirectory . DS . $storyId;

        return is_dir($storyPath) && file_exists($storyPath . DS . 'metadata.json');
    }
}