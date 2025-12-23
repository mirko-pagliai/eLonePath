<?php
declare(strict_types=1);

namespace eLonePath\Test\Stat;

use eLonePath\Stat\Skill;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Skill::class)]
class SkillTest extends TestCase
{
    /**
     * @throws \Random\RandomException
     */
    #[Test]
    public function testRollRandom(): void
    {
        $skill = Skill::rollRandom();
        $this->assertGreaterThanOrEqual(7, $skill->current);
        $this->assertLessThanOrEqual(12, $skill->current);
    }
}
