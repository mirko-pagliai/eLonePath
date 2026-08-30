<?php
declare(strict_types=1);

namespace Test\Utility;

use App\Utility\Dice;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * DiceTest.
 */
#[CoversClass(Dice::class)]
class DiceTest extends TestCase
{
    /**
     * @link \App\Utility\Dice::roll()
     */
    #[Test]
    public function testRoll(): void
    {
        $result = new Dice()->roll();
        $this->assertGreaterThanOrEqual(1, $result);
        $this->assertLessThanOrEqual(6, $result);
    }

    /**
     * @link \App\Utility\Dice::rollDouble()
     */
    #[Test]
    public function testRollDouble(): void
    {
        $result = new Dice()->rollDouble();
        $this->assertCount(2, $result);
        foreach ($result as $dice) {
            $this->assertGreaterThanOrEqual(1, $dice);
            $this->assertLessThanOrEqual(6, $dice);
        }
    }
}
