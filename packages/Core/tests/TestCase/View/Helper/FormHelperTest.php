<?php
declare(strict_types=1);

namespace Elone\Core\Test\View\Helper;

use Elone\Core\Exception\RouteNotFoundException;
use Elone\Core\Server\Request;
use Elone\Core\View\Helper\FormHelper;
use Elone\Core\View\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * FormHelperTest.
 */
#[CoversClass(FormHelper::class)]
class FormHelperTest extends TestCase
{
    private FormHelper $helper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->helper = new FormHelper(new View());
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::create()
     */
    #[Test]
    public function testCreateWithRoute(): void
    {
        $result = $this->helper->create(['controller' => 'Pages', 'action' => 'postOnly']);

        $this->assertSame('<form method="post" action="/pages/postOnly">', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::create()
     */
    #[Test]
    public function testCreateWithStringUrl(): void
    {
        $result = $this->helper->create('/pages/postOnly');

        $this->assertSame('<form method="post" action="/pages/postOnly">', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::create()
     */
    #[Test]
    public function testCreateWithExtraOptions(): void
    {
        $result = $this->helper->create('/pages/postOnly', ['class' => 'needs-validation']);

        $this->assertSame(
            '<form method="post" action="/pages/postOnly" class="needs-validation">',
            $result,
        );
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::create()
     */
    #[Test]
    public function testCreateWithInvalidRoute(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->helper->create([]);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::label()
     */
    #[Test]
    public function testLabel(): void
    {
        $result = $this->helper->label('name', 'Name');

        $this->assertSame('<label for="name" class="form-label">Name</label>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::label()
     */
    #[Test]
    public function testLabelEscapesForAndText(): void
    {
        $result = $this->helper->label('a"b', 'A & B');

        $this->assertSame('<label for="a&quot;b" class="form-label">A &amp; B</label>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::input()
     */
    #[Test]
    public function testInputDefaultsToTextType(): void
    {
        $result = $this->helper->input('name', 'Name');

        $this->assertSame(
            '<div class="mb-3"><label for="name" class="form-label">Name</label>'
            . '<input type="text" class="form-control" id="name" name="name"></div>',
            $result,
        );
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::input()
     */
    #[Test]
    public function testInputWithNumberTypeAndAttributes(): void
    {
        $result = $this->helper->input(
            'age',
            'Age',
            ['type' => 'number', 'min' => 1, 'required' => true],
        );

        $this->assertSame(
            '<div class="mb-3"><label for="age" class="form-label">Age</label>'
            . '<input type="number" class="form-control" id="age" name="age" min="1" required="required"></div>',
            $result,
        );
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::input()
     */
    #[Test]
    public function testInputWithMinAndMax(): void
    {
        $result = $this->helper->input(
            'age',
            'Age',
            ['type' => 'number', 'min' => 18, 'max' => 99, 'required' => true],
        );

        $this->assertSame(
            '<div class="mb-3"><label for="age" class="form-label">Age</label>'
            . '<input type="number" class="form-control" id="age" name="age" min="18" max="99" required="required"></div>',
            $result,
        );
    }

    /**
     * The label is HTML-escaped — it's developer-written text today, but nothing here assumes that stays true.
     *
     * @link \Elone\Core\View\Helper\FormHelper::input()
     */
    #[Test]
    public function testInputEscapesLabel(): void
    {
        $result = $this->helper->input('name', 'A & B');

        $this->assertStringContainsString('<label for="name" class="form-label">A &amp; B</label>', $result);
    }

    /**
     * With no `value` given explicitly, the field is pre-filled from the current request's own submitted data —
     * this is what makes a form re-show what was typed after a failed validation.
     *
     * @link \Elone\Core\View\Helper\FormHelper::input()
     */
    #[Test]
    public function testInputPrefillsValueFromTheRequest(): void
    {
        $request = new Request('POST', '/', ['strength' => '7']);
        $helper = new FormHelper(new View($request));

        $result = $helper->input('strength', 'Forza');

        $this->assertStringContainsString('value="7"', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::input()
     */
    #[Test]
    public function testInputWithNoMatchingRequestDataHasNoValue(): void
    {
        $request = new Request('POST', '/', ['other' => '7']);
        $helper = new FormHelper(new View($request));

        $result = $helper->input('strength', 'Forza');

        $this->assertStringNotContainsString('value=', $result);
    }

    /**
     * A `View` built with no request at all — `$this->helper`, from `setUp()` — has nothing to pre-fill from.
     *
     * @link \Elone\Core\View\Helper\FormHelper::input()
     */
    #[Test]
    public function testInputWithNoRequestHasNoValue(): void
    {
        $result = $this->helper->input('strength', 'Forza');

        $this->assertStringNotContainsString('value=', $result);
    }

    /**
     * An explicit `value` in `$options` wins over whatever the current request's own data has for that field.
     *
     * @link \Elone\Core\View\Helper\FormHelper::input()
     */
    #[Test]
    public function testInputExplicitValueOverridesTheRequest(): void
    {
        $request = new Request('POST', '/', ['strength' => '7']);
        $helper = new FormHelper(new View($request));

        $result = $helper->input('strength', 'Forza', ['value' => '99']);

        $this->assertStringContainsString('value="99"', $result);
        $this->assertStringNotContainsString('value="7"', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::end()
     */
    #[Test]
    public function testEnd(): void
    {
        $result = $this->helper->end();

        $this->assertSame('</form>', $result);
    }

    /**
     * Test with a full `create()`/`input()`/`end()` sequence.
     *
     * @link \Elone\Core\View\Helper\FormHelper::create()
     * @link \Elone\Core\View\Helper\FormHelper::input()
     * @link \Elone\Core\View\Helper\FormHelper::end()
     */
    #[Test]
    public function testFullFormSequence(): void
    {
        $expected = '<form method="post" action="/pages/postOnly">'
            . '<div class="mb-3"><label for="age" class="form-label">Age</label>'
            . '<input type="number" class="form-control" id="age" name="age" required="required"></div>'
            . '</form>';

        $result = $this->helper->create(['controller' => 'Pages', 'action' => 'postOnly'])
            . $this->helper->input('age', 'Age', ['type' => 'number', 'required' => true])
            . $this->helper->end();

        $this->assertSame($expected, $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::button()
     */
    #[Test]
    public function testButtonDefaultsToTypeButton(): void
    {
        $this->assertSame('<button type="button">Cancel</button>', $this->helper->button('Cancel'));
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::button()
     */
    #[Test]
    public function testButtonWithExplicitTypeAndOptions(): void
    {
        $result = $this->helper->button('Go', ['type' => 'submit', 'class' => 'btn btn-primary']);

        $this->assertSame('<button type="submit" class="btn btn-primary">Go</button>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::button()
     */
    #[Test]
    public function testButtonEscapesLabelByDefault(): void
    {
        $this->assertSame('<button type="button">A &amp; B</button>', $this->helper->button('A & B'));
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::button()
     */
    #[Test]
    public function testButtonWithEscapeFalseRendersRawLabel(): void
    {
        $result = $this->helper->button('<strong>Go</strong>', ['escape' => false]);

        $this->assertSame('<button type="button"><strong>Go</strong></button>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::submit()
     */
    #[Test]
    public function testSubmitWithDefaultLabel(): void
    {
        $this->assertSame('<button type="submit">Submit</button>', $this->helper->submit());
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::submit()
     */
    #[Test]
    public function testSubmitWithLabelAndOptions(): void
    {
        $result = $this->helper->submit('Create character', ['class' => 'btn btn-primary']);

        $this->assertSame('<button type="submit" class="btn btn-primary">Create character</button>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::reset()
     */
    #[Test]
    public function testResetWithDefaultLabel(): void
    {
        $this->assertSame('<button type="reset">Reset</button>', $this->helper->reset());
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::reset()
     */
    #[Test]
    public function testResetWithCustomLabel(): void
    {
        $this->assertSame('<button type="reset">Clear</button>', $this->helper->reset('Clear'));
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::hidden()
     */
    #[Test]
    public function testHidden(): void
    {
        $result = $this->helper->hidden('token', 'abc123');

        $this->assertSame('<input type="hidden" id="token" name="token" value="abc123">', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::hidden()
     */
    #[Test]
    public function testHiddenEscapesValue(): void
    {
        $result = $this->helper->hidden('name', 'A & "B"');

        $this->assertSame('<input type="hidden" id="name" name="name" value="A &amp; &quot;B&quot;">', $result);
    }
}
