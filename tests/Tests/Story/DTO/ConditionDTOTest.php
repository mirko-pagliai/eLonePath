<?php
declare(strict_types=1);

namespace eLonePath\Test\Story\DTO;

use eLonePath\Story\ConditionType;
use eLonePath\Story\DTO\ConditionDTO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConditionDTO::class)]
class ConditionDTOTest extends TestCase
{
    #[Test]
    public function fromArray(): void
    {
        $result = ConditionDTO::fromArray([
            'type' => ConditionType::COMBAT_WON->value,
        ]);

        $this->assertSame(ConditionType::COMBAT_WON, $result->type);
        $this->assertSame([], $result->data);

        $result = ConditionDTO::fromArray([
            'type' => ConditionType::HAS_ITEM->value,
            'item' => 'key',
        ]);

        $this->assertSame(ConditionType::HAS_ITEM, $result->type);
        $this->assertSame(['item' => 'key'], $result->data);
    }

    /**
     * @param string $expectedExceptionMessage
     * @param array<string, mixed> $data
     */
    #[Test]
    #[TestWith(['Condition missing "type".', []])]
    #[TestWith(['Condition missing "type".', ['type' => '']])]
    #[TestWith(['Invalid condition type: `noExistingType`.', ['type' => 'noExistingType']])]
    public function fromArrayOnError(string $expectedExceptionMessage, array $data): void
    {
        $this->expectExceptionMessage($expectedExceptionMessage);
        // @phpstan-ignore argument.type
        ConditionDTO::fromArray($data);
    }
}
