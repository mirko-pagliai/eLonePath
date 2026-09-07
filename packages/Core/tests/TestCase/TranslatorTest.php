<?php
declare(strict_types=1);

namespace Elone\Core\Test;

use Elone\Core\Translator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Translator::class)]
class TranslatorTest extends TestCase
{
    /**
     * @link \Elone\Core\Translator::translate()
     */
    public function testTranslate(): void
    {
        Translator::init('it');

        $expected = 'Buongiorno';
        $result = Translator::translate('default', 'Good morning');
        $this->assertSame($expected, $result);
    }
}
