<?php
declare(strict_types=1);

namespace eLonePath\Story\DTO;

use InvalidArgumentException;

/**
 * Data Transfer Object for story paragraphs from JSON.
 *
 * Immutable object representing paragraph data parsed from story files.
 */
readonly class ParagraphDTO
{
    /**
     * Create a paragraph DTO.
     *
     * @param int $id The unique identifier for the paragraph. Must be a positive integer.
     * @param string $text The content of the paragraph. Cannot be an empty string.
     * @param \eLonePath\Story\DTO\EventDTO|null $event An optional event associated with the paragraph.
     * @param array<\eLonePath\Story\DTO\ChoiceDTO> $choices An array of choices related to the paragraph.
     * @return void
     * @throws \InvalidArgumentException If the $id is not positive or if $text is empty.
     */
    public function __construct(
        public int $id,
        public string $text,
        public ?EventDTO $event = null,
        public array $choices = [],
    ) {
        if ($id < 1) {
            throw new InvalidArgumentException('Paragraph ID must be positive.');
        }
        if (trim($text) === '') {
            throw new InvalidArgumentException('Paragraph text cannot be empty.');
        }
    }

    /**
     * Create ParagraphDTO from array data.
     *
     * @param int $id Paragraph ID
     * @param array{
     *     text: string,
     *     event?: array{type: string, ...},
     *     choices?: array<array{
     *         text: string,
     *         target: int,
     *         condition?: array{type: string, item?: string, value?: int}
     *     }>
     * } $data Paragraph data from JSON
     * @return self
     * @throws \InvalidArgumentException If required fields are missing or invalid
     */
    public static function fromArray(int $id, array $data): self
    {
        if (empty($data['text'])) {
            throw new InvalidArgumentException('Paragraph #' . $id . ' missing "text".');
        }

        if (isset($data['event'])) {
            try {
                $event = EventDTO::fromArray($data['event']);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException('Paragraph #' . $id . ': ' . lcfirst($e->getMessage()) . '.');
            }
        }

        if (isset($data['choices'])) {
            foreach ($data['choices'] as $choiceData) {
                try {
                    $choices[] = ChoiceDTO::fromArray($choiceData);
                } catch (InvalidArgumentException $e) {
                    throw new InvalidArgumentException('Paragraph #' . $id . ': ' . lcfirst(rtrim($e->getMessage(), '.')) . '.');
                }
            }
        }

        return new self(id: $id, text: $data['text'], event: $event ?? null, choices: $choices ?? []);
    }
}
