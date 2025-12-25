<?php
declare(strict_types=1);

namespace eLonePath\Story\DTO;

use eLonePath\Story\EventType;
use InvalidArgumentException;
use ValueError;

/**
 * Data Transfer Object for paragraph events from JSON.
 *
 * Immutable object representing event data parsed from story files.
 */
readonly class EventDTO
{
    /**
     * Create an event DTO.
     *
     * @param \eLonePath\Story\EventType $type Event type
     * @param array<string, mixed> $data Additional event data (enemy stats, item name, stat changes, etc.)
     */
    public function __construct(
        public EventType $type,
        public array $data = [],
    ) {
    }

    /**
     * Create EventDTO from array data.
     *
     * @param array{type: string, ...} $data Event data from JSON
     * @return self
     * @throws \InvalidArgumentException If the type is missing or invalid
     */
    public static function fromArray(array $data): self
    {
        if (empty($data['type'])) {
            throw new InvalidArgumentException('Event missing "type"');
        }

        try {
            $type = EventType::from($data['type']);
        } catch (ValueError) {
            throw new InvalidArgumentException("Invalid event type: `{$data['type']}`");
        }

        $eventData = $data;
        unset($eventData['type']);

        return new self($type, $eventData);
    }
}
