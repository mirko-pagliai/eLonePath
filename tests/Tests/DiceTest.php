<?php
declare(strict_types=1);

namespace eLonePath\Test;

use eLonePath\Dice;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Dice::class)]
class DiceTest extends TestCase
{
    /**
     * @throws \Random\RandomException
     */
    #[Test]
    public function testRollD6(): void
    {
        $roll = Dice::rollD6();
        $this->assertGreaterThanOrEqual(1, $roll);
        $this->assertLessThanOrEqual(6, $roll);
    }

    /**
     * @throws \Random\RandomException
     */
    #[Test]
    public function testRoll2D6(): void
    {
        $roll = Dice::roll2D6();
        $this->assertGreaterThanOrEqual(2, $roll);
        $this->assertLessThanOrEqual(12, $roll);
    }

    /**
     * @throws \Random\RandomException
     */
    #[Test]
    public function testRollMultipleD6(): void
    {
        $roll = Dice::rollMultipleD6(3);
        $this->assertGreaterThanOrEqual(3, $roll);
        $this->assertLessThanOrEqual(24, $roll);
    }
}
