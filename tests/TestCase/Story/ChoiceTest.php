<?php
declare(strict_types=1);

namespace Test\Story;

use App\Story\Choice;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * ChoiceTest.
 */
#[CoversClass(Choice::class)]
class ChoiceTest extends TestCase
{
    /**
     * @link \App\Story\Choice::__construct()
     */
    #[Test]
    #[TestWith(['Go to page {{page}}', 5, 'Go to page 5'])]
    #[TestWith(['Vai a pagina {{page}} ora', 12, 'Vai a pagina 12 ora'])]
    #[TestWith(['No placeholder here', 3, 'No placeholder here'])]
    #[TestWith(['{{page}} and {{page}} again', 7, '7 and 7 again'])]
    public function testConstructReplacesPagePlaceholder(string $content, int $target, string $expectedContent): void
    {
        $choice = new Choice($content, $target);

        $this->assertSame($expectedContent, $choice->content);
        $this->assertSame($target, $choice->target);
    }

    /**
     * @link \App\Story\Choice::createFromArray()
     */
    #[Test]
    public function testCreateFromArray(): void
    {
        $choice = Choice::createFromArray(['content' => 'Go to page {{page}}', 'target' => 9]);

        $this->assertSame('Go to page 9', $choice->content);
        $this->assertSame(9, $choice->target);
    }
}
