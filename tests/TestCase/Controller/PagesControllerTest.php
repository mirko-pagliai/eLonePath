<?php
declare(strict_types=1);

namespace Test\TestCase\Controller;

use App\Controller\PagesController;
use App\Exception\DocumentNotFoundException;
use Elone\Core\Server\Request;
use Elone\Core\Translator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * PagesControllerTest.
 */
#[CoversClass(PagesController::class)]
class PagesControllerTest extends TestCase
{
    /**
     * `Translator`'s own locale is static, global state — it survives past the end of whichever test last
     * called `Translator::init()` with something other than `'en'`, and would otherwise leak into every test
     * that runs after this file, in any other file, regardless of whether it touches `Translator` itself.
     *
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        Translator::init('en');
    }

    /**
     * @link \App\Controller\PagesController::home()
     */
    #[Test]
    public function testHomeTemplateExists(): void
    {
        $controller = new PagesController(new Request('GET', '/'));

        $controller->home();
        $result = $controller->render('Pages/home', layout: null);

        $this->assertNotSame('', trim($result));
    }

    /**
     * @link \App\Controller\PagesController::stories()
     */
    #[Test]
    public function testStoriesTemplateExists(): void
    {
        $controller = new PagesController(new Request('GET', '/'));

        $controller->stories();
        $result = $controller->render('Pages/stories', layout: null);

        $this->assertNotSame('', trim($result));
    }

    /**
     * `docs()` lists only the documentation pages available in the currently detected locale.
     *
     * @link \App\Controller\PagesController::docs()
     * @link \Elone\Core\Translator::getLocale()
     */
    #[Test]
    public function testDocsListsFilesForTheCurrentLocale(): void
    {
        Translator::init('it');
        $controller = new PagesController(new Request('GET', '/pages/docs'));

        $controller->docs();

        $files = $controller->getView()->get('files');
        $this->assertIsArray($files);
        $this->assertCount(1, $files);
        $file = $files[0];
        $this->assertIsArray($file);
        $this->assertSame('sample', $file['basename']);
        $this->assertSame('Titolo di prova', $file['title']);
    }

    /**
     * Same file, different locale — a different title, read from the matching language's own copy.
     *
     * @link \App\Controller\PagesController::docs()
     */
    #[Test]
    public function testDocsListsFilesForADifferentLocale(): void
    {
        Translator::init('en');
        $controller = new PagesController(new Request('GET', '/pages/docs'));

        $controller->docs();

        $files = $controller->getView()->get('files');
        $this->assertIsArray($files);
        $file = $files[0];
        $this->assertIsArray($file);
        $this->assertSame('Sample Title', $file['title']);
    }

    /**
     * A locale with no documentation directory of its own lists no files — not an error, just nothing found.
     *
     * @link \App\Controller\PagesController::docs()
     */
    #[Test]
    public function testDocsWithNoFilesForLocaleReturnsEmptyArray(): void
    {
        Translator::init('fr');
        $controller = new PagesController(new Request('GET', '/pages/docs'));

        $controller->docs();

        $this->assertSame([], $controller->getView()->get('files'));
    }

    /**
     * @link \App\Controller\PagesController::doc()
     */
    #[Test]
    public function testDoc(): void
    {
        Translator::init('it');
        $controller = new PagesController(new Request('GET', '/pages/doc/sample'));

        $controller->doc('sample');

        $content = $controller->getView()->get('content');
        $this->assertIsString($content);
        $this->assertStringContainsString('Contenuto di prova, in italiano.', $content);
    }

    /**
     * Same basename, different locale — the matching language's own file.
     *
     * @link \App\Controller\PagesController::doc()
     */
    #[Test]
    public function testDocWithDifferentLocale(): void
    {
        Translator::init('en');
        $controller = new PagesController(new Request('GET', '/pages/doc/sample'));

        $controller->doc('sample');

        $content = $controller->getView()->get('content');
        $this->assertIsString($content);
        $this->assertStringContainsString('Sample content, in English.', $content);
    }

    /**
     * Anything outside letters, digits, hyphens, and underscores is rejected outright — including the classic
     * path-traversal attempt — before ever touching the filesystem.
     *
     * @link \App\Controller\PagesController::doc()
     */
    #[Test]
    #[TestWith(['../../../etc/passwd'])]
    #[TestWith(['sample.php'])]
    #[TestWith(['sample/../secret'])]
    #[TestWith(['sample\\windows'])]
    public function testDocWithInvalidBasenameThrows(string $basename): void
    {
        Translator::init('it');
        $controller = new PagesController(new Request('GET', "/pages/doc/$basename"));

        $this->expectException(DocumentNotFoundException::class);
        $this->expectExceptionMessageIs("The basename `$basename` contains invalid characters.");
        $controller->doc($basename);
    }

    /**
     * A syntactically valid basename that simply doesn't match any file — same exception, same status code,
     * as an invalid one; from the outside, a missing page and a malformed request both just mean "not found".
     *
     * @link \App\Controller\PagesController::doc()
     */
    #[Test]
    public function testDocWithNonExistentFileThrows(): void
    {
        Translator::init('it');
        $controller = new PagesController(new Request('GET', '/pages/doc/does-not-exist'));

        try {
            $controller->doc('does-not-exist');
            $this->fail('Expected a DocumentNotFoundException.');
        } catch (DocumentNotFoundException $exception) {
            $this->assertSame(404, $exception->statusCode());
            $this->assertStringContainsString('does-not-exist.md` does not exist.', $exception->getMessage());
        }
    }
}
