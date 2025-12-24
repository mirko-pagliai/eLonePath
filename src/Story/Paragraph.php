<?php
declare(strict_types=1);

namespace eLonePath\Story;

use InvalidArgumentException;
use ValueError;

/**
 * Story paragraph.
 *
 * Represents a single paragraph in the story with text, optional event, and choices.
 */
class Paragraph
{
    /**
     * Paragraph ID.
     */
    public int $id;

    /**
     * Paragraph text.
     */
    public string $text;

    /**
     * Optional event type that occurs in this paragraph.
     */
    public ?EventType $eventType;

    /**
     * Optional event data.
     *
     * @var array<string, mixed>
     */
    public array $eventData;

    /**
     * Available choices.
     *
     * @var array<\eLonePath\Story\Choice>
     */
    public array $choices;

    /**
     * Create a paragraph.
     *
     * @param int $id Paragraph ID
     * @param string $text Paragraph text
     * @param \eLonePath\Story\EventType|null $eventType Optional event type
     * @param array<string, mixed> $eventData Optional event data
     * @param array<\eLonePath\Story\Choice> $choices Available choices
     */
    public function __construct(
        int $id,
        string $text,
        ?EventType $eventType = null,
        array $eventData = [],
        array $choices = [],
    ) {
        if ($id < 1) {
            throw new InvalidArgumentException('Paragraph ID must be positive');
        }
        if (trim($text) === '') {
            throw new InvalidArgumentException('Paragraph text cannot be empty');
        }

        $this->id = $id;
        $this->text = trim($text);
        $this->eventType = $eventType;
        $this->eventData = $eventData;
        $this->choices = $choices;
    }

    /**
     * Check if this paragraph has an event.
     *
     * @return bool
     */
    public function hasEvent(): bool
    {
        return $this->eventType !== null;
    }

    /**
     * Check if this paragraph is an ending (no choices available).
     *
     * @return bool
     */
    public function isEnding(): bool
    {
        return empty($this->choices);
    }

    /**
     * Export the Paragraph data to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'text' => $this->text,
        ];

        if ($this->eventType !== null) {
            $data['event'] = array_merge(
                ['type' => $this->eventType->value],
                $this->eventData,
            );
        }

        if (!empty($this->choices)) {
            $data['choices'] = array_map(fn(Choice $choice) => $choice->toArray(), $this->choices);
        }

        return $data;
    }

    /**
     * Create the Paragraph from array data.
     *
     * @param int $id Paragraph ID
     * @param array{
     *     text: string,
     *     event?: array{type: string, ...},
     *     choices?: array<array{text: string, target: int, condition?: array{type: string, item?: string, value?: int}}>
     * } $data Paragraph data
     * @return self
     */
    public static function fromArray(int $id, array $data): self
    {
        if (empty($data['text'])) {
            throw new InvalidArgumentException("Paragraph {$id} missing 'text'");
        }

        $eventData = [];

        if (isset($data['event'])) {
            if (empty($data['event']['type'])) {
                throw new InvalidArgumentException("Paragraph {$id} event missing 'type'");
            }

            try {
                $eventType = EventType::from($data['event']['type']);
            } catch (ValueError) {
                throw new InvalidArgumentException(
                    "Paragraph {$id} has invalid event type: `{$data['event']['type']}`",
                );
            }

            unset($data['event']['type']);
            $eventData = $data['event'];
        }

        $choices = array_map(fn(array $choiceData) => Choice::fromArray($choiceData), $data['choices'] ?? []);

        return new self(
            id: $id,
            text: $data['text'],
            eventType: $eventType ?? null,
            eventData: $eventData ?? [],
            choices: $choices,
        );
    }
}
