<?php
declare(strict_types=1);

namespace Elone\Core\Test;

use Elone\Core\Translator;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * `__()`/`__d()` are thin wrappers around `Translator::translate()` — `TranslatorTest`'s own coverage applies
 * just as much here, called through the global functions instead of the static method directly.
 */
#[CoversFunction('__')]
#[CoversFunction('__d')]
class GlobalFunctionsTest extends TestCase
{
    /**
     * @link \__()
     */
    #[Test]
    public function test__(): void
    {
        Translator::init('it');

        $expected = 'Buongiorno';
        $result = __('Good morning');
        $this->assertSame($expected, $result);
    }

    /**
     * @link \__()
     */
    #[Test]
    public function test__WithPlaceholders(): void
    {
        Translator::init('it');

        $expected = 'Ciao Frank, io sono Mark';
        $result = __('Hello {0}, I\'m {1}', 'Frank', 'Mark');
        $this->assertSame($expected, $result);

        $expected = 'Noi siamo 2 persone';
        $result = __('We are {0} people', 2);
        $this->assertSame($expected, $result);
    }

    /**
     * Test for `__()` using plurals.
     *
     * @link \__()
     */
    #[Test]
    #[TestWith(['Nessun risultato', 0])]
    #[TestWith(['1 risultato', 1])]
    #[TestWith(['2 risultati', 2])]
    public function test__WithPlurals(string $expected, int $number): void
    {
        Translator::init('it');

        $result = __('{0,plural,=0{No records found} =1{Found 1 record} other{Found # records}}', $number);
        $this->assertSame($expected, $result);
    }

    /**
     * Test for `__d()`, using a custom domain.
     *
     * @link \__d()
     */
    #[Test]
    public function test__d(): void
    {
        Translator::init('it');

        $expected = 'Errore: `404`';
        $result = __d('validation', 'Error: `{0}`', 404);
        $this->assertSame($expected, $result);
    }

    /**
     * @link \__()
     * @link \__d()
     */
    #[Test]
    public function testMissingMessageFallsBackToTheSourceString(): void
    {
        $expected = 'This message does not exist';
        Translator::init('it');
        $result = __($expected);
        $this->assertSame($expected, $result);

        /**
         * These strings exist, but only for `it`, not for `fr`.
         *
         * So the fallback is still expected for both cases.
         */
        $expected = 'Good morning';
        Translator::init('fr');
        $result = __($expected);
        $this->assertSame($expected, $result);

        Translator::init('fr');
        $expected = 'Error: `404`';
        $result = __d('validation', 'Error: `{0}`', 404);
        $this->assertSame($expected, $result);
    }

    /**
     * Expects `Translator` to be initialized with `en` by default, when `__()` is called without `init()` first.
     *
     * @link \__()
     */
    #[Test]
    public function testWithoutInit(): void
    {
        $expected = 'Good morning';
        $result = __('Good morning');
        $this->assertSame($expected, $result);
    }
}
