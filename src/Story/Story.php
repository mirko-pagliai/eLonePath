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
     * @var array<int, \eLonePath\Story\Paragraph>
     */
    private array $paragraphs;

    /**
     * Create a story.
     *
     * @param string $title Story title
     * @param string $author Story author
     * @param string $description Story description
     * @param int $initialGold Initial gold amount
     * @param array<int, \eLonePath\Story\Paragraph> $paragraphs Paragraphs indexed by ID
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

        array_walk($paragraphs, [$this, 'addParagraph']);
    }

    /**
     * Add a paragraph to the story.
     *
     * @param \eLonePath\Story\Paragraph $paragraph
     * @return self
     */
    public function addParagraph(Paragraph $paragraph): self
    {
        $this->paragraphs[$paragraph->id] = $paragraph;

        return $this;
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
            throw new InvalidArgumentException("Paragraph with ID `{$id}` does not exist in this story");
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
            $errors[] = 'Story must have a starting paragraph with ID `#1`';
        }

        // Check all choice targets
        foreach ($this->paragraphs as $paragraph) {
            foreach ($paragraph->choices as $choice) {
                if (!$this->hasParagraph($choice->target)) {
                    $errors[] = sprintf(
                        'Paragraph with ID `#%d`: choice "%s" points to non-existent `#%d` target paragraph',
                        $paragraph->id,
                        $choice->text,
                        $choice->target,
                    );
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
        return [
            'metadata' => [
                'title' => $this->title,
                'author' => $this->author,
                'description' => $this->description,
                'initial_gold' => $this->initialGold,
            ],
            'paragraphs' => array_map(fn($paragraph): array => $paragraph->toArray(), $this->paragraphs),
        ];
    }

    /**
     * Create the Story from array data.
     *
     * @param array{
     *      metadata: array{
     *          title: string,
     *          author: string,
     *          description?: string,
     *          initial_gold?: int,
     *      },
     *      paragraphs: array<int, array{
     *          text: string,
     *          event?: array{type: string, ...},
     *          choices?: array<array{
     *              text: string,
     *              target: int,
     *              condition?: array{
     *                  type: string,
     *                  item?: string,
     *                  value?: int,
     *              },
     *          }>,
     *      }>,
     * } $data Story data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        if (empty($data['metadata'])) {
            throw new InvalidArgumentException('Story data missing "metadata"');
        }
        if (empty($data['metadata']['title'])) {
            throw new InvalidArgumentException('Metadata missing "title"');
        }
        if (empty($data['metadata']['author'])) {
            throw new InvalidArgumentException('Metadata missing "author"');
        }
        if (empty($data['paragraphs'])) {
            throw new InvalidArgumentException('Story data missing "paragraphs"');
        }

        return new self(
            title: $data['metadata']['title'],
            author: $data['metadata']['author'],
            description: $data['metadata']['description'] ?? '',
            initialGold: $data['metadata']['initial_gold'] ?? 10,
            paragraphs: array_map(
                fn(array $paragraphData, int $id): Paragraph => Paragraph::fromArray($id, $paragraphData),
                $data['paragraphs'],
                array_keys($data['paragraphs']),
            ),
        );
    }
}
