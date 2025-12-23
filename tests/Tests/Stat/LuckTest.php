<?php
declare(strict_types=1);

namespace eLonePath\Test\Stat;

use eLonePath\Stats\Luck;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Luck::class)]
class LuckTest extends TestCase
{
    /**
     * @throws \Random\RandomException
     */
    #[Test]
    public function testRollRandom(): void
    {
        $luck = Luck::rollRandom();
        $this->assertGreaterThanOrEqual(7, $luck->current);
        $this->assertLessThanOrEqual(12, $luck->current);
    }

    /**
     * @throws \Random\RandomException
     */
    #[Test]
    public function testTest(): void
    {
        $luck = Luck::rollRandom();
        $luck->check();
        $this->assertLessThanOrEqual(11, $luck->current);
    }
}
