<?php
declare(strict_types=1);

namespace eLonePath\Story;

use InvalidArgumentException;

/**
 * Story container.
 *
 * Represents a complete gamebook story with metadata and paragraphs.
 */
class Story
{
    /**
     * Story title.
     */
    public string $title;

    /**
     * Story author.
     */
    public string $author;

    /**
     * Story description.
     */
    public string $description;

    /**
     * Initial gold amount for new characters.
     */
    public int $initialGold;

    /**
     * Collection of paragraphs indexed by ID.
     *
     * @var array<int, Paragraph>
     */
    private array $paragraphs;

    /**
     * Create a story.
     *
     * @param string $title Story title
     * @param string $author Story author
     * @param string $description Story description
     * @param int $initialGold Initial gold amount
     * @param array<int, Paragraph> $paragraphs Paragraphs indexed by ID
     */
    public function __construct(
        string $title,
        string $author,
        string $description,
        int $initialGold,
        array $paragraphs = []
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

        $this->title = trim($title);
        $this->author = trim($author);
        $this->description = trim($description);
        $this->initialGold = $initialGold;
        $this->paragraphs = [];

        foreach ($paragraphs as $paragraph) {
            $this->addParagraph($paragraph);
        }
    }

    /**
     * Add a paragraph to the story.
     *
     * @param \eLonePath\Story\Paragraph $paragraph
     * @return void
     */
    public function addParagraph(Paragraph $paragraph): void
    {
        $this->paragraphs[$paragraph->id] = $paragraph;
    }

    /**
     * Get a paragraph by ID.
     *
     * @param int $id Paragraph ID
     * @return \eLonePath\Story\Paragraph
     * @throws \InvalidArgumentException If the paragraph does not exist
     */
    public function getParagraph(int $id): Paragraph
    {
        if (!isset($this->paragraphs[$id])) {
            throw new InvalidArgumentException("Paragraph {$id} does not exist in this story");
        }

        return $this->paragraphs[$id];
    }

    /**
     * Check if a paragraph exists.
     *
     * @param int $id Paragraph ID
     * @return bool
     */
    public function hasParagraph(int $id): bool
    {
        return isset($this->paragraphs[$id]);
    }

    /**
     * Get all paragraphs.
     *
     * @return array<int, \eLonePath\Story\Paragraph>
     */
    public function getAllParagraphs(): array
    {
        return $this->paragraphs;
    }

    /**
     * Get the starting paragraph (always ID 1).
     *
     * @return \eLonePath\Story\Paragraph
     * @throws \InvalidArgumentException If paragraph 1 does not exist
     */
    public function getStartingParagraph(): Paragraph
    {
        return $this->getParagraph(1);
    }

    /**
     * Validate that all choice targets point to existing paragraphs.
     *
     * @return array<string> List of validation errors (empty if valid)
     */
    public function validate(): array
    {
        $errors = [];

        // Check that paragraph 1 exists
        if (!$this->hasParagraph(1)) {
            $errors[] = 'Story must have a starting paragraph with ID 1';
        }

        // Check all choice targets
        foreach ($this->paragraphs as $paragraph) {
            foreach ($paragraph->choices as $choice) {
                if (!$this->hasParagraph($choice->target)) {
                    $errors[] = "Paragraph {$paragraph->id}: Choice '{$choice->text}' points to non-existent paragraph {$choice->target}";
                }
            }
        }

        return $errors;
    }

    /**
     * Export the Story data to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $paragraphsArray = array_map(function ($paragraph) {
            return $paragraph->toArray();
        }, $this->paragraphs);

        return [
            'metadata' => [
                'title' => $this->title,
                'author' => $this->author,
                'description' => $this->description,
                'initial_gold' => $this->initialGold,
            ],
            'paragraphs' => $paragraphsArray,
        ];
    }

    /**
     * Create the Story from array data.
     *
     * @param array<string, mixed> $data Story data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['metadata'])) {
            throw new InvalidArgumentException('Story data missing "metadata"');
        }
        if (!isset($data['paragraphs'])) {
            throw new InvalidArgumentException('Story data missing "paragraphs"');
        }

        $metadata = $data['metadata'];

        $story = new self(
            title: $metadata['title'] ?? throw new InvalidArgumentException('Metadata missing "title"'),
            author: $metadata['author'] ?? throw new InvalidArgumentException('Metadata missing "author"'),
            description: $metadata['description'] ?? '',
            initialGold: $metadata['initial_gold'] ?? 10,
        );

        foreach ($data['paragraphs'] as $id => $paragraphData) {
            $paragraph = Paragraph::fromArray((int) $id, $paragraphData);
            $story->addParagraph($paragraph);
        }

        return $story;
    }
}
