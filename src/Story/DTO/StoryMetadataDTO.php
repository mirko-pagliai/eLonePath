<?php
declare(strict_types=1);

namespace eLonePath\Story\DTO;

use InvalidArgumentException;

/**
 * Data Transfer Object for story metadata from JSON.
 *
 * Immutable object representing metadata parsed from story files.
 */
readonly class StoryMetadataDTO
{
    /**
     * Create a story metadata DTO.
     *
     * @param string $title Story title
     * @param string $author Story author
     * @param string $description Story description
     * @param int $initialGold Initial gold amount for new characters
     */
    public function __construct(
        public string $title,
        public string $author,
        public string $description = '',
        public int $initialGold = 10,
    ) {
        if (trim($title) === '') {
            throw new InvalidArgumentException('Story title cannot be empty');
        }
        if (trim($author) === '') {
            throw new InvalidArgumentException('Story author cannot be empty');
        }
        if ($initialGold < 0) {
            throw new InvalidArgumentException('Initial gold cannot be negative');
        }
    }

    /**
     * Create StoryMetadataDTO from array data.
     *
     * @param array{
     *     title: string,
     *     author: string,
     *     description?: string,
     *     initial_gold?: int
     * } $data Metadata from JSON
     * @return self
     * @throws \InvalidArgumentException If required fields are missing or invalid
     */
    public static function fromArray(array $data): self
    {
        if (empty($data['title'])) {
            throw new InvalidArgumentException('Metadata missing "title"');
        }
        if (empty($data['author'])) {
            throw new InvalidArgumentException('Metadata missing "author"');
        }

        return new self(
            title: $data['title'],
            author: $data['author'],
            description: $data['description'] ?? '',
            initialGold: $data['initial_gold'] ?? 10,
        );
    }
}
