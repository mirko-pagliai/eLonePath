<?php
declare(strict_types=1);

namespace Tests\Engine\Stat;

use eLonePath\Engine\Stats\Stat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Stat::class)]
class StatTest extends TestCase
{
    #[Test]
    public function testConstruct(): void
    {
        $stat = new class (10, 5) extends Stat {
        };
        $this->assertSame(10, $stat->max);
        $this->assertSame(5, $stat->current);
    }

    #[Test]
    public function testDecrease(): void
    {
        $stat = new class (10, 5) extends Stat {
        };
        $stat->decrease(2);
        $this->assertSame(3, $stat->current);
    }

    #[Test]
    public function testIncrease(): void
    {
        $stat = new class (10, 5) extends Stat {
        };
        $stat->increase(2);
        $this->assertSame(7, $stat->current);
    }

    #[Test]
    public function testRestore(): void
    {
        $stat = new class (10, 5) extends Stat {
        };
        $stat->restore();
        $this->assertSame(10, $stat->current);
    }

    #[Test]
    public function testIsAtMax(): void
    {
        $stat = new class (10, 5) extends Stat {
        };
        $this->assertFalse($stat->isAtMax());

        $stat->restore();
        $this->assertTrue($stat->isAtMax());
    }

    #[Test]
    public function testIsGreaterThan(): void
    {
        $stat = new class (10) extends Stat {
        };
        $this->assertTrue($stat->isGreaterThan(5));
        $this->assertFalse($stat->isGreaterThan(11));
    }

    #[Test]
    public function testToArray(): void
    {
        $stat = new class (10, 5) extends Stat {
        };
        $this->assertSame(['current' => 5, 'max' => 10], $stat->toArray());
    }

    #[Test]
    public function testFromArray(): void
    {
        $stat = new class (10, 5) extends Stat {
        };
        $stat = $stat::fromArray(['max' => 12, 'current' => 6]);
        $this->assertSame(Stat::class, get_parent_class($stat));
        $this->assertSame(6, $stat->current);
        $this->assertSame(12, $stat->max);
    }
}
