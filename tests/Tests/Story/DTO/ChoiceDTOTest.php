<?php
declare(strict_types=1);

namespace eLonePath\Test\Story\DTO;

use eLonePath\Story\ConditionType;
use eLonePath\Story\DTO\ChoiceDTO;
use eLonePath\Story\DTO\ConditionDTO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChoiceDTO::class)]
class ChoiceDTOTest extends TestCase
{
    #[Test]
    #[TestWith(['Choice text cannot be empty', '', 2])]
    #[TestWith(['Choice text cannot be empty', '  ', 2])]
    #[TestWith(['Target paragraph ID must be positive', 'A choice', 0])]
    #[TestWith(['Target paragraph ID must be positive', 'A choice', -1])]
    public function testConstructOnError(string $expectedExceptionMessage, string $text, int $target): void
    {
        $this->expectExceptionMessage($expectedExceptionMessage);
        new ChoiceDTO(text: $text, target: $target);
    }

    #[Test]
    public function testFromArray(): void
    {
        $choice = ChoiceDTO::fromArray([
            'text' => 'Go north',
            'target' => 5,
        ]);
        $this->assertSame('Go north', $choice->text);
        $this->assertSame(5, $choice->target);
        $this->assertNull($choice->condition);

        $choice = ChoiceDTO::fromArray([
            'text' => 'Go north',
            'target' => 5,
            'condition' => [
                'type' => ConditionType::HAS_ITEM->value,
                'item' => 'key',
            ],
        ]);
        $this->assertSame('Go north', $choice->text);
        $this->assertSame(5, $choice->target);
        $this->assertEquals(
            new ConditionDTO(type: ConditionType::HAS_ITEM, data: ['item' => 'key']),
            $choice->condition,
        );
    }

    /**
     * @param string $expectedExceptionMessage
     * @param array<string, mixed> $data
     */
    #[Test]
    #[TestWith(['Choice missing "text".', []])]
    #[TestWith(['Choice missing "text".', ['text' => '']])]
    #[TestWith(['Choice missing "target".', ['text' => 'A choice']])]
    #[TestWith(['Choice missing "target".', ['text' => 'A choice', 'target' => '']])]
    public function fromArrayOnError(string $expectedExceptionMessage, array $data): void
    {
        $this->expectExceptionMessage($expectedExceptionMessage);
        // @phpstan-ignore argument.type
        ChoiceDTO::fromArray($data);
    }
}
