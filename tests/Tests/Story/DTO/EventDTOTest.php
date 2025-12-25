<?php
declare(strict_types=1);

namespace eLonePath\Test\Story\DTO;

use eLonePath\Story\DTO\EventDTO;
use eLonePath\Story\EventType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventDTO::class)]
class EventDTOTest extends TestCase
{
    #[Test]
    public function fromArray(): void
    {
        $result = EventDTO::fromArray([
            'type' => EventType::COMBAT->value,
        ]);

        $this->assertSame(EventType::COMBAT, $result->type);
        $this->assertSame([], $result->data);
    }

    /**
     * @param string $expectedExceptionMessage
     * @param array<string, mixed> $data
     */
    #[Test]
    #[TestWith(['Event missing "type".', []])]
    #[TestWith(['Event missing "type".', ['type' => '']])]
    #[TestWith(['Invalid event type: `noExistingType`.', ['type' => 'noExistingType']])]
    public function fromArrayOnError(string $expectedExceptionMessage, array $data): void
    {
        $this->expectExceptionMessage($expectedExceptionMessage);
        // @phpstan-ignore argument.type
        EventDTO::fromArray($data);
    }
}
