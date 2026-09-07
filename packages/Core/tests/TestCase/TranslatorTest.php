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

    /**
     * @link \Elone\Core\Translator::translate()
     */
    public function testTranslateWithPlaceholders(): void
    {
        Translator::init('it');

        $expected = 'Ciao Frank, io sono Mark';
        $result = Translator::translate('default', 'Hello {0}, I\'m {1}', 'Frank', 'Mark');
        $this->assertSame($expected, $result);

        $expected = 'Noi siamo 2 persone';
        $result = Translator::translate('default', 'We are {0} people', 2);
        $this->assertSame($expected, $result);
    }

    /**
     * @link \Elone\Core\Translator::translate()
     */
    public function testTranslateMissingMessage(): void
    {
        $expected = 'This message does not exist';
        Translator::init('it');
        $result = Translator::translate('default', 'This message does not exist');
        $this->assertSame($expected, $result);

        /**
         * This string exists, but only for `it`, not for `fr`.
         * So the fallback is still expected.
         */
        $expected = 'Good morning';
        Translator::init('fr');
        $result = Translator::translate('default', 'Good morning');
        $this->assertSame($expected, $result);
    }

    /**
     * Test for the `translate()` method when the translator is not initialized.
     *
     * It expects it to be initialized with `en` by default.
     *
     * @link \Elone\Core\Translator::translate()
     */
    public function testTranslateWithoutInit(): void
    {
        $expected = 'Good morning';
        $result = Translator::translate('default', 'Good morning');
        $this->assertSame($expected, $result);
    }
}
