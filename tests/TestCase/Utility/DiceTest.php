<?php
declare(strict_types=1);

namespace Test\Utility;

use App\Utility\Dice;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
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

    /**
     * @link \App\Utility\Dice::rollMultiple()
     */
    #[Test]
    #[TestWith([1])]
    #[TestWith([2])]
    #[TestWith([5])]
    public function testRollMultiple(int $count): void
    {
        $result = new Dice()->rollMultiple($count);

        $this->assertCount($count, $result);
        foreach ($result as $roll) {
            $this->assertGreaterThanOrEqual(1, $roll);
            $this->assertLessThanOrEqual(6, $roll);
        }
    }

    /**
     * @link \App\Utility\Dice::rollMultiple()
     */
    #[Test]
    public function testRollMultipleWithZero(): void
    {
        $result = new Dice()->rollMultiple(0);
        $this->assertSame([], $result);
    }
}
