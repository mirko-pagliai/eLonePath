<?php
declare(strict_types=1);

namespace Elone\Core\Test\TestCase\View\Helper;

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
     * A `templates` override passed to the constructor replaces the default markup for that entry — the rest of
     * `label()`'s behavior (escaping, `for` from `$for`) is unaffected.
     *
     * @link \Elone\Core\View\Helper\FormHelper::__construct()
     * @link \Elone\Core\View\Helper\FormHelper::label()
     */
    #[Test]
    public function testConstructWithTemplatesOverridesLabel(): void
    {
        $helper = new FormHelper(new View(), ['templates' => ['label' => '<label{{attrs}}>{{text}}</label><br>']]);

        $result = $helper->label('name', 'Name');

        $this->assertSame('<label for="name" class="form-label">Name</label><br>', $result);
    }

    /**
     * Every method builds its markup from `$templates`, not a hardcoded string — one override per entry,
     * verified against the method that uses it. `input()` itself is bare now, so the `inputContainer` override
     * is verified through `control()`, which is what actually uses it.
     *
     * @link \Elone\Core\View\Helper\FormHelper::__construct()
     * @link \Elone\Core\View\Helper\FormHelper::create()
     * @link \Elone\Core\View\Helper\FormHelper::control()
     * @link \Elone\Core\View\Helper\FormHelper::hidden()
     * @link \Elone\Core\View\Helper\FormHelper::button()
     * @link \Elone\Core\View\Helper\FormHelper::end()
     */
    #[Test]
    public function testConstructWithTemplatesOverridesEveryEntry(): void
    {
        $helper = new FormHelper(new View(), ['templates' => [
            'formStart' => '<form data-turbo="false" action="{{action}}"{{attrs}}>',
            'input' => '<input type="{{type}}" class="custom-input"{{attrs}}>',
            'inputContainer' => '<p>{{label}} {{input}}</p>',
            'hiddenInput' => '<input type="hidden" data-secret="true"{{attrs}}>',
            'button' => '<button class="btn"{{attrs}}>{{text}}</button>',
            'formEnd' => '<!-- end --></form>',
        ]]);

        $this->assertSame('<form data-turbo="false" action="/x">', $helper->create('/x'));
        $this->assertSame(
            '<p><label for="name" class="form-label">Name</label> '
            . '<input type="text" class="custom-input" id="name" name="name"></p>',
            $helper->control('name', 'Name'),
        );
        $this->assertSame(
            '<input type="hidden" data-secret="true" id="token" name="token" value="abc">',
            $helper->hidden('token', 'abc'),
        );
        $this->assertSame('<button class="btn">Go</button>', $helper->button('Go'));
        $this->assertSame('<!-- end --></form>', $helper->end());
    }

    /**
     * Bare `<input>` — no label, no wrapping container. See `control()` for the complete version.
     *
     * @link \Elone\Core\View\Helper\FormHelper::input()
     */
    #[Test]
    public function testInputDefaultsToTextType(): void
    {
        $result = $this->helper->input('name');

        $this->assertSame('<input type="text" class="form-control" id="name" name="name">', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::input()
     */
    #[Test]
    public function testInputWithNumberTypeAndAttributes(): void
    {
        $result = $this->helper->input('age', ['type' => 'number', 'min' => 1, 'required' => true]);

        $this->assertSame(
            '<input type="number" class="form-control" id="age" name="age" min="1" required="required">',
            $result,
        );
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

        $result = $helper->input('strength');

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

        $result = $helper->input('strength');

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
        $result = $this->helper->input('strength');

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

        $result = $helper->input('strength', ['value' => '99']);

        $this->assertStringContainsString('value="99"', $result);
        $this->assertStringNotContainsString('value="7"', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::select()
     */
    #[Test]
    public function testSelect(): void
    {
        $result = $this->helper->select('color', ['r' => 'Red', 'g' => 'Green']);

        $this->assertSame(
            '<select class="form-select" id="color" name="color">'
            . '<option value="r">Red</option><option value="g">Green</option></select>',
            $result,
        );
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::select()
     */
    #[Test]
    public function testSelectWithValueAndRequired(): void
    {
        $result = $this->helper->select(
            'color',
            ['r' => 'Red', 'g' => 'Green'],
            ['value' => 'g', 'required' => true],
        );

        $this->assertSame(
            '<select class="form-select" id="color" name="color" required="required">'
            . '<option value="r">Red</option><option value="g" selected>Green</option></select>',
            $result,
        );
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::select()
     */
    #[Test]
    public function testSelectPrefillsSelectedFromTheRequest(): void
    {
        $request = new Request('POST', '/', ['color' => 'g']);
        $helper = new FormHelper(new View($request));

        $result = $helper->select('color', ['r' => 'Red', 'g' => 'Green']);

        $this->assertStringContainsString('<option value="g" selected>Green</option>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::select()
     */
    #[Test]
    public function testSelectExplicitValueOverridesTheRequest(): void
    {
        $request = new Request('POST', '/', ['color' => 'g']);
        $helper = new FormHelper(new View($request));

        $result = $helper->select('color', ['r' => 'Red', 'g' => 'Green'], ['value' => 'r']);

        $this->assertStringContainsString('<option value="r" selected>Red</option>', $result);
        $this->assertStringNotContainsString('selected>Green', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::checkbox()
     */
    #[Test]
    public function testCheckbox(): void
    {
        $result = $this->helper->checkbox('terms');

        $this->assertSame(
            '<input class="form-check-input" type="checkbox" id="terms" name="terms" value="1">',
            $result,
        );
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::checkbox()
     */
    #[Test]
    public function testCheckboxChecked(): void
    {
        $result = $this->helper->checkbox('terms', '1', ['checked' => true]);

        $this->assertStringContainsString('checked="checked"', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::checkbox()
     */
    #[Test]
    public function testCheckboxWithCustomValue(): void
    {
        $result = $this->helper->checkbox('newsletter', 'yes');

        $this->assertStringContainsString('value="yes"', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::radio()
     */
    #[Test]
    public function testRadio(): void
    {
        $result = $this->helper->radio('color', 'red');

        $this->assertSame(
            '<input class="form-check-input" type="radio" id="color-red" name="color" value="red">',
            $result,
        );
    }

    /**
     * Every radio in the same group shares `name`, but needs its own `id` — the default (`"$name-$value"`)
     * guarantees this without the caller having to supply one for every option.
     *
     * @link \Elone\Core\View\Helper\FormHelper::radio()
     */
    #[Test]
    public function testRadioGroupSharesNameWithDistinctIds(): void
    {
        $red = $this->helper->radio('color', 'red');
        $blue = $this->helper->radio('color', 'blue');

        $this->assertStringContainsString('id="color-red"', $red);
        $this->assertStringContainsString('id="color-blue"', $blue);
        $this->assertStringContainsString('name="color"', $red);
        $this->assertStringContainsString('name="color"', $blue);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::radio()
     */
    #[Test]
    public function testRadioChecked(): void
    {
        $result = $this->helper->radio('color', 'red', ['checked' => true]);

        $this->assertStringContainsString('checked="checked"', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::radio()
     */
    #[Test]
    public function testRadioWithExplicitId(): void
    {
        $result = $this->helper->radio('color', 'red', ['id' => 'custom-id']);

        $this->assertStringContainsString('id="custom-id"', $result);
    }

    /**
     * The default `type`, `'text'` — a complete, labeled field.
     *
     * @link \Elone\Core\View\Helper\FormHelper::control()
     */
    #[Test]
    public function testControlDefaultsToText(): void
    {
        $result = $this->helper->control('name', 'Name');

        $this->assertSame(
            '<div class="mb-3 text"><label for="name" class="form-label">Name</label>'
            . '<input type="text" class="form-control" id="name" name="name"></div>',
            $result,
        );
    }

    /**
     * The container's own class always includes `type`, plus `required` only when the field is one.
     *
     * @link \Elone\Core\View\Helper\FormHelper::control()
     */
    #[Test]
    public function testControlContainerClassReflectsTypeAndRequired(): void
    {
        $result = $this->helper->control('age', 'Age', ['type' => 'number', 'required' => true]);

        $this->assertStringStartsWith('<div class="mb-3 number required">', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::control()
     */
    #[Test]
    public function testControlWithSelectType(): void
    {
        $result = $this->helper->control('color', 'Color', [
            'type' => 'select',
            'choices' => ['r' => 'Red', 'g' => 'Green'],
            'required' => true,
        ]);

        $this->assertSame(
            '<div class="mb-3 select required"><label for="color" class="form-label">Color</label>'
            . '<select class="form-select" id="color" name="color" required="required">'
            . '<option value="r">Red</option><option value="g">Green</option></select></div>',
            $result,
        );
    }

    /**
     * `checkbox` gets its own wrapper — `.form-check`, not `.mb-3` — and its own label template
     * (`checkLabel`).
     *
     * @link \Elone\Core\View\Helper\FormHelper::control()
     */
    #[Test]
    public function testControlWithCheckboxType(): void
    {
        $result = $this->helper->control('terms', 'Accept terms', ['type' => 'checkbox']);

        $this->assertSame(
            '<div class="form-check"><input class="form-check-input" type="checkbox" id="terms" name="terms"'
            . ' value="1"><label for="terms" class="form-check-label">Accept terms</label></div>',
            $result,
        );
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::control()
     */
    #[Test]
    public function testControlWithCheckboxTypeChecked(): void
    {
        $result = $this->helper->control('terms', 'Accept terms', ['type' => 'checkbox', 'checked' => true]);

        $this->assertStringContainsString('checked="checked"', $result);
    }

    /**
     * `radio` builds one `.form-check` block per `choices` entry, all sharing `name`, wrapped together in the
     * same `mb-3` container a `select` or text field would get.
     *
     * @link \Elone\Core\View\Helper\FormHelper::control()
     */
    #[Test]
    public function testControlWithRadioType(): void
    {
        $result = $this->helper->control('color', 'Color', [
            'type' => 'radio',
            'choices' => ['r' => 'Red', 'b' => 'Blue'],
            'value' => 'b',
        ]);

        $this->assertSame(
            '<div class="mb-3 radio"><label for="color" class="form-label">Color</label>'
            . '<div class="form-check"><input class="form-check-input" type="radio" id="color-r" name="color"'
            . ' value="r"><label for="color-r" class="form-check-label">Red</label></div>'
            . '<div class="form-check"><input class="form-check-input" type="radio" id="color-b" name="color"'
            . ' value="b" checked="checked"><label for="color-b" class="form-check-label">Blue</label></div>'
            . '</div>',
            $result,
        );
    }

    /**
     * `checkLabel` is its own template, independent of `label` — customizing one never touches the other.
     *
     * @link \Elone\Core\View\Helper\FormHelper::control()
     */
    #[Test]
    public function testControlCheckboxUsesItsOwnLabelTemplate(): void
    {
        $helper = new FormHelper(new View(), ['templates' => ['checkLabel' => '<label{{attrs}}>*{{text}}</label>']]);

        $checkboxResult = $helper->control('terms', 'Accept', ['type' => 'checkbox']);
        $textResult = $helper->control('name', 'Name');

        $this->assertStringContainsString('*Accept', $checkboxResult);
        $this->assertStringContainsString('<label for="name" class="form-label">Name</label>', $textResult);
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
     * Test with a full `create()`/`control()`/`end()` sequence.
     *
     * @link \Elone\Core\View\Helper\FormHelper::create()
     * @link \Elone\Core\View\Helper\FormHelper::control()
     * @link \Elone\Core\View\Helper\FormHelper::end()
     */
    #[Test]
    public function testFullFormSequence(): void
    {
        $expected = '<form method="post" action="/pages/postOnly">'
            . '<div class="mb-3 number required"><label for="age" class="form-label">Age</label>'
            . '<input type="number" class="form-control" id="age" name="age" required="required"></div>'
            . '</form>';

        $result = $this->helper->create(['controller' => 'Pages', 'action' => 'postOnly'])
            . $this->helper->control('age', 'Age', ['type' => 'number', 'required' => true])
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
