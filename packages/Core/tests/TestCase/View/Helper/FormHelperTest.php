<?php
declare(strict_types=1);

namespace Elone\Core\Test\View\Helper;

use Elone\Core\Exception\RouteNotFoundException;
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
    private FormHelper $formHelper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->formHelper = new FormHelper(new View());
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::create()
     */
    #[Test]
    public function testCreateWithRoute(): void
    {
        $result = $this->formHelper->create(['controller' => 'Story', 'action' => 'character', 'la-torre']);

        $this->assertSame('<form method="post" action="/story/character/la-torre">', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::create()
     */
    #[Test]
    public function testCreateWithStringUrl(): void
    {
        $result = $this->formHelper->create('/story/character/la-torre');

        $this->assertSame('<form method="post" action="/story/character/la-torre">', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::create()
     */
    #[Test]
    public function testCreateWithExtraOptions(): void
    {
        $result = $this->formHelper->create('/story/character/la-torre', ['class' => 'needs-validation']);

        $this->assertSame(
            '<form method="post" action="/story/character/la-torre" class="needs-validation">',
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
        $this->formHelper->create([]);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::input()
     */
    #[Test]
    public function testInputDefaultsToTextType(): void
    {
        $result = $this->formHelper->input('name', 'Nome');

        $this->assertSame(
            '<div class="mb-3"><label for="name" class="form-label">Nome</label>'
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
        $result = $this->formHelper->input('strength', 'Forza', ['type' => 'number', 'min' => 1, 'required' => true]);

        $this->assertSame(
            '<div class="mb-3"><label for="strength" class="form-label">Forza</label>'
            . '<input type="number" class="form-control" id="strength" name="strength" min="1" required="1"></div>',
            $result,
        );
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::input()
     */
    #[Test]
    public function testInputWithMinAndMax(): void
    {
        $result = $this->formHelper->input(
            'perception',
            'Percezione',
            ['type' => 'number', 'min' => 1, 'max' => 5, 'required' => true],
        );

        $this->assertSame(
            '<div class="mb-3"><label for="perception" class="form-label">Percezione</label>'
            . '<input type="number" class="form-control" id="perception" name="perception" min="1" max="5"'
            . ' required="1"></div>',
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
        $result = $this->formHelper->input('name', 'A & B');

        $this->assertStringContainsString('<label for="name" class="form-label">A &amp; B</label>', $result);
    }

    /**
     * @link \Elone\Core\View\Helper\FormHelper::end()
     */
    #[Test]
    public function testEnd(): void
    {
        $result = $this->formHelper->end();

        $this->assertSame('</form>', $result);
    }

    /**
     * A full `create()`/`input()`/`end()` sequence, matching the exact shape `templates/Story/character.php`
     * builds — this is what proves the pieces compose into a real, submittable form, not just that each one
     * works in isolation.
     *
     * @link \Elone\Core\View\Helper\FormHelper::create()
     * @link \Elone\Core\View\Helper\FormHelper::input()
     * @link \Elone\Core\View\Helper\FormHelper::end()
     */
    #[Test]
    public function testFullFormSequence(): void
    {
        $html = $this->formHelper->create(['controller' => 'Story', 'action' => 'character', 'la-torre'])
            . $this->formHelper->input('strength', 'Forza', ['type' => 'number', 'min' => 1, 'required' => true])
            . $this->formHelper->end();

        $this->assertSame(
            '<form method="post" action="/story/character/la-torre">'
            . '<div class="mb-3"><label for="strength" class="form-label">Forza</label>'
            . '<input type="number" class="form-control" id="strength" name="strength" min="1" required="1"></div>'
            . '</form>',
            $html,
        );
    }
}
