<?php
declare(strict_types=1);

namespace eLonePath\Test\View;

use eLonePath\View\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass( View::class)]
class ViewTest extends TestCase
{
    #[Test]
    public function testSetLayout(): void
    {
        $view = new class extends View {
            public ?string $layout;
        };

        $this->assertSame('layouts/default.php', $view->layout);

        $view->setLayout('layouts/custom.php');
        $this->assertSame('layouts/custom.php', $view->layout);
    }

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
