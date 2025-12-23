<?php
declare(strict_types=1);

namespace eLonePath\Story;

use InvalidArgumentException;

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
     * Optional event that occurs in this paragraph.
     */
    public ?EventInterface $event;

    /**
     * Available choices.
     *
     * @var array<Choice>
     */
    public array $choices;

    /**
     * Create a paragraph.
     *
     * @param int $id Paragraph ID
     * @param string $text Paragraph text
     * @param \eLonePath\Story\EventInterface|null $event Optional event
     * @param array<Choice> $choices Available choices
     */
    public function __construct(int $id, string $text, ?EventInterface $event = null, array $choices = [])
    {
        if ($id < 1) {
            throw new InvalidArgumentException('Paragraph ID must be positive');
        }
        if (trim($text) === '') {
            throw new InvalidArgumentException('Paragraph text cannot be empty');
        }

        $this->id = $id;
        $this->text = trim($text);
        $this->event = $event;
        $this->choices = $choices;
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
     * Export paragraph data to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'text' => $this->text,
        ];

        if ($this->event !== null) {
            $data['event'] = $this->event->toArray();
        }

        if (!empty($this->choices)) {
            $data['choices'] = array_map(fn(Choice $choice) => $choice->toArray(), $this->choices);
        }

        return $data;
    }

    /**
     * Create paragraph from array data.
     *
     * @param int $id Paragraph ID
     * @param array<string, mixed> $data Paragraph data
     * @return self
     */
    public static function fromArray(int $id, array $data): self
    {
        $text = $data['text'] ?? throw new InvalidArgumentException("Paragraph {$id} missing 'text'");

        $event = null;
        if (isset($data['event'])) {
            $event = self::createEventFromArray($data['event']);
        }

        $choices = [];
        if (isset($data['choices'])) {
            foreach ($data['choices'] as $choiceData) {
                $choices[] = Choice::fromArray($choiceData);
            }
        }

        return new self($id, $text, $event, $choices);
    }

    /**
     * Create an event instance from array data.
     *
     * @param array<string, mixed> $data Event data
     * @return EventInterface
     */
    private static function createEventFromArray(array $data): EventInterface
    {
        $type = $data['type'] ?? throw new InvalidArgumentException('Event missing "type"');

        return match ($type) {
            'combat' => CombatEvent::fromArray($data),
            'add_item' => AddItemEvent::fromArray($data),
            'modify_stat' => ModifyStatEvent::fromArray($data),
            'modify_gold' => ModifyGoldEvent::fromArray($data),
            default => throw new InvalidArgumentException("Unknown event type: {$type}"),
        };
    }
}
