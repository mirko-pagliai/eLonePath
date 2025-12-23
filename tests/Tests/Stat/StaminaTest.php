<?php
declare(strict_types=1);

namespace eLonePath\Test\Stat;

use eLonePath\Stats\Stamina;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Stamina::class)]
class StaminaTest extends TestCase
{
    /**
     * @throws \Random\RandomException
     */
    #[Test]
    public function testRollRandom(): void
    {
        $stamina = Stamina::rollRandom();
        $this->assertGreaterThanOrEqual(14, $stamina->current);
        $this->assertLessThanOrEqual(24, $stamina->current);
    }
}
