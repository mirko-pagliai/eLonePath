<?php
declare(strict_types=1);

namespace eLonePath\Test\View;

use eLonePath\View\View;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass( View::class)]
class ViewTest extends TestCase
{
    #[Test]
    public function testSet(): void
    {
        $expected = [
            'key' => 'value',
            'secondKey' => 'secondValue',
            'thirdKey' => 'thirdValue',
        ];

        $view = new class extends View {
            public array $data = [];
        };
        $view->set(['key' => 'value']);
        $view->set(['secondKey' => 'secondValue', 'thirdKey' => 'thirdValue']);

        $this->assertSame($expected, $view->data);
    }

    #[Test]
    public function testSetWithKeyAlreadyExists(): void
    {
        $view = new View();
        $view->set(['myKey' => 'value']);

        $this->expectExceptionMessage('Data key `myKey` already exists.');
        $view->set(['myKey' => 'newValue']);
    }

    #[Test]
    public function testAutoDetectTemplateWithoutRequest(): void
    {
        $view = new class extends View {
            public function autoDetectTemplate(): string
            {
                return parent::autoDetectTemplate();
            }
        };

        $this->expectExceptionMessage('Request not set. Call `setRequest()` before `render()`.');
        $view->autoDetectTemplate();
    }

    #[Test]
    public function testAutoDetectTemplateWithoutControllerAttribute(): void
    {
        $view = new class extends View {
            public function autoDetectTemplate(): string
            {
                return parent::autoDetectTemplate();
            }
        };

        $request = Request::create('/test');
        $view->setRequest($request);

        $this->expectExceptionMessage('Controller not found in request attributes.');
        $view->autoDetectTemplate();
    }

    /**
     * @param array{class-string, non-empty-string} $controller
     * @param string $expected
     */
    #[Test]
    #[TestWith([['eLonePath\Controller\HomeController', 'index'], 'Home/index.php'])]
    #[TestWith([['eLonePath\Controller\HomeController', 'showProfile'], 'Home/show_profile.php'])]
    #[TestWith([['eLonePath\Controller\UserController', 'editSettings'], 'User/edit_settings.php'])]
    #[TestWith([['App\Controller\AdminController', 'dashboard'], 'Admin/dashboard.php'])]
    #[TestWith([['MyApp\SomeController', 'myAction'], 'Some/my_action.php'])]
    #[TestWith([['Controller', 'test'], '/test.php'])]
    #[TestWith([['FooBarController', 'bazQux'], 'FooBar/baz_qux.php'])]
    public function testAutoDetectTemplateWithArrayController(array $controller, string $expected): void
    {
        $view = new class extends View {
            public function autoDetectTemplate(): string
            {
                return parent::autoDetectTemplate();
            }
        };

        $request = Request::create('/test');
        $request->attributes->set('_controller', $controller);
        $view->setRequest($request);

        $result = $view->autoDetectTemplate();
        $this->assertSame($expected, $result);
    }

    #[Test]
    #[TestWith(['eLonePath\Controller\HomeController::index', 'Home/index.php'])]
    #[TestWith(['eLonePath\Controller\HomeController::showProfile', 'Home/show_profile.php'])]
    #[TestWith(['eLonePath\Controller\UserController::editSettings', 'User/edit_settings.php'])]
    #[TestWith(['App\Controller\AdminController::dashboard', 'Admin/dashboard.php'])]
    #[TestWith(['MyApp\SomeController::myAction', 'Some/my_action.php'])]
    #[TestWith(['Controller::test', '/test.php'])]
    #[TestWith(['FooBarController::bazQux', 'FooBar/baz_qux.php'])]
    public function testAutoDetectTemplateWithStringController(string $controller, string $expected): void
    {
        $view = new class extends View {
            public function autoDetectTemplate(): string
            {
                return parent::autoDetectTemplate();
            }
        };

        $request = Request::create('/test');
        $request->attributes->set('_controller', $controller);
        $view->setRequest($request);

        $result = $view->autoDetectTemplate();
        $this->assertSame($expected, $result);
    }

    /**
     * @param array{class-string, non-empty-string} $controller
     * @param string $expected
     */
    #[Test]
    #[TestWith([['eLonePath\Controller\APIController', 'getUsers'], 'API/get_users.php'])]
    #[TestWith([['App\Controller\XMLController', 'parseXML'], 'XML/parse_xml.php'])]
    #[TestWith([['MyApp\HTMLParserController', 'convertHTML'], 'HTMLParser/convert_html.php'])]
    #[TestWith([['App\Controller\OAuth2Controller', 'validateToken'], 'OAuth2/validate_token.php'])]
    public function testAutoDetectTemplateWithAcronyms(array $controller, string $expected): void
    {
        $view = new class extends View {
            public function autoDetectTemplate(): string
            {
                return parent::autoDetectTemplate();
            }
        };

        $request = Request::create('/test');
        $request->attributes->set('_controller', $controller);
        $view->setRequest($request);

        $result = $view->autoDetectTemplate();
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function testAutoDetectTemplateRemovesControllerSuffix(): void
    {
        $view = new class extends View {
            public function autoDetectTemplate(): string
            {
                return parent::autoDetectTemplate();
            }
        };

        $request = Request::create('/test');
        $request->attributes->set('_controller', ['App\MyTestController', 'index']);
        $view->setRequest($request);

        $result = $view->autoDetectTemplate();

        $this->assertSame('MyTest/index.php', $result);
    }

    #[Test]
    public function testAutoDetectTemplateWithoutControllerSuffix(): void
    {
        $view = new class extends View {
            public function autoDetectTemplate(): string
            {
                return parent::autoDetectTemplate();
            }
        };

        $request = Request::create('/test');
        $request->attributes->set('_controller', ['App\MyTest', 'index']);
        $view->setRequest($request);

        $result = $view->autoDetectTemplate();

        $this->assertSame('MyTest/index.php', $result);
    }

    #[Test]
    public function testAutoDetectTemplateWithDeepNamespace(): void
    {
        $view = new class extends View {
            public function autoDetectTemplate(): string
            {
                return parent::autoDetectTemplate();
            }
        };

        $request = Request::create('/test');
        $request->attributes->set('_controller', ['Very\Deep\Namespace\Structure\HomeController', 'index']);
        $view->setRequest($request);

        $result = $view->autoDetectTemplate();

        $this->assertSame('Home/index.php', $result);
    }

    #[Test]
    #[TestWith(['index', 'index'])]
    #[TestWith(['myAction', 'my_action'])]
    #[TestWith(['showProfile', 'show_profile'])]
    #[TestWith(['editUserSettings', 'edit_user_settings'])]
    #[TestWith(['list', 'list'])]
    #[TestWith(['create', 'create'])]
    #[TestWith(['update', 'update'])]
    #[TestWith(['delete', 'delete'])]
    #[TestWith(['HTMLParser', 'html_parser'])]
    #[TestWith(['parseHTML', 'parse_html'])]
    #[TestWith(['getHTMLContent', 'get_html_content'])]
    #[TestWith(['XMLToJSON', 'xml_to_json'])]
    #[TestWith(['APIController', 'api_controller'])]
    #[TestWith(['getURLFromAPI', 'get_url_from_api'])]
    #[TestWith(['HTTPSConnection', 'https_connection'])]
    #[TestWith(['item2Json', 'item2_json'])]
    #[TestWith(['base64Encode', 'base64_encode'])]
    #[TestWith(['utf8ToAscii', 'utf8_to_ascii'])]
    #[TestWith(['version2Migration', 'version2_migration'])]
    #[TestWith(['indexAction', 'index_action'])]
    #[TestWith(['showAction', 'show_action'])]
    #[TestWith(['newAction', 'new_action'])]
    #[TestWith(['createAction', 'create_action'])]
    #[TestWith(['editAction', 'edit_action'])]
    #[TestWith(['updateAction', 'update_action'])]
    #[TestWith(['deleteAction', 'delete_action'])]
    #[TestWith(['getUserProfileById', 'get_user_profile_by_id'])]
    #[TestWith(['exportDataToCSV', 'export_data_to_csv'])]
    #[TestWith(['importCSVData', 'import_csv_data'])]
    #[TestWith(['validateOAuthToken', 'validate_o_auth_token'])]
    #[TestWith(['processPayPalPayment', 'process_pay_pal_payment'])]
    #[TestWith(['convertPDFToImage', 'convert_pdf_to_image'])]
    #[TestWith(['a', 'a'])]
    #[TestWith(['A', 'a'])]
    #[TestWith(['AB', 'ab'])]
    #[TestWith(['ABC', 'abc'])]
    #[TestWith(['ABCDef', 'abc_def'])]
    #[TestWith(['aB', 'a_b'])]
    #[TestWith(['aBC', 'a_bc'])]
    #[TestWith(['aBCDef', 'a_bc_def'])]
    #[TestWith(['my_action', 'my_action'])]
    #[TestWith(['get_user_profile', 'get_user_profile'])]
    #[TestWith(['api_controller', 'api_controller'])]
    public function testCamelToSnake(string $input, string $expected): void
    {
        $view = new class extends View {
            public function camelToSnake(string $input): string
            {
                return parent::camelToSnake($input);
            }
        };

        $result = $view->camelToSnake($input);
        $this->assertSame($expected, $result, "Failed: '{$input}' expected '{$expected}', got '{$result}'");
    }
}
