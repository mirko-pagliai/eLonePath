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
     * Constructs the file path to the directory of a specific story based on its ID.
     *
     * @param string $storyId The ID of the story for which to construct the file path.
     * @return string The file path to the specified story's directory.
     */
    protected function getStoryPath(string $storyId): string
    {
        return $this->storiesDirectory . DS . $storyId;
    }

    /**
     * Retrieves the metadata for a given story.
     *
     * @param string $storyId The identifier of the story.
     * @return array{
     *      title: string,
     *      author: string,
     *      description?: string,
     *      initial_gold?: int,
     * } The metadata of the story as an associative array.
     */
    protected function getMetadata(string $storyId): array
    {
        /** @phpstan-ignore return.type */
        return Filesystem::readJsonDataFromFile($this->getStoryPath($storyId) . DS . 'metadata.json');
    }

    /**
     * Retrieves an array of paragraphs from a JSON file corresponding to the given story ID.
     *
     * @param string $storyId The ID of the story for which to retrieve paragraphs.
     * @return array<int, array{
     *      text: string,
     *      event?: array{type: string, ...},
     *      choices?: array<array{
     *          text: string,
     *          target: int,
     *          condition?: array{
     *              type: string,
     *              item?: string,
     *              value?: int,
     *          },
     *      }>,
     * }> An array of paragraphs, or an empty array if none are found.
     */
    protected function getParagraphs(string $storyId): array
    {
        /** @phpstan-ignore return.type */
        return Filesystem::readJsonDataFromFile($this->getStoryPath($storyId) . DS . 'en.json')['paragraphs'] ?? [];
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
        $story = Story::fromArray([
            'metadata' => $this->getMetadata($storyId),
            'paragraphs' => $this->getParagraphs($storyId),
        ]);

        $validationErrors = $story->validate();
        if (!empty($validationErrors)) {
            throw new InvalidArgumentException(
                "Story validation failed in `{$this->getStoryPath($storyId)}`:\n- " . implode("\n- ", $validationErrors)
            );
        }

        return $story;
    }

    /**
     * List all available story files.
     *
     * @return array<string, array{title: string, author: string, description?: string}> List of stories with their metadata.
     */
    public function listAvailableStories(): array
    {
        $stories = [];

        $directories = glob($this->storiesDirectory . DS . '*', GLOB_ONLYDIR) ?: [];

        foreach ($directories as $directory) {
            $metadata = $this->getMetadata(basename($directory));
            unset($metadata['initial_gold']);
            $stories[basename($directory)] = $metadata;
        }

        if (empty($stories)) {
            throw new RuntimeException("No stories found in `{$this->storiesDirectory}` directory.");
        }

        return $stories;
    }
}