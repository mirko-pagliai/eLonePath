<?php
declare(strict_types=1);

namespace Elone\Core\Test\View;

use Elone\Core\View\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * ViewTest.
 */
#[CoversClass(View::class)]
class ViewTest extends TestCase
{
    /**
     * @link \Elone\Core\View\View::get()
     */
    #[Test]
    public function testGet(): void
    {
        $view = new View()->set(['key1' => 'value1']);

        $result = $view->get(name: 'noExisting');
        $this->assertSame(null, $result);

        $result = $view->get(name: 'noExisting', default: 'defaultValue');
        $this->assertSame('defaultValue', $result);

        $result = $view->get(name: 'key1');
        $this->assertSame('value1', $result);

        $result = $view->get(name: 'key1', default: 'ignoredDefaultValue');
        $this->assertSame('value1', $result);
    }

    /**
     * @link \Elone\Core\View\View::set()
     */
    #[Test]
    public function testSet(): void
    {
        $view = new View();

        $result = $view->set(['key1' => 'value1', 'key2' => 'value2']);
        $this->assertSame($result, $view);

        $this->assertSame('value1', $view->get('key1'));
        $this->assertSame('value2', $view->get('key2'));

        $view->set(['key2' => 'newValue']);

        $this->assertSame('value1', $view->get('key1'));
        $this->assertSame('newValue', $view->get('key2'));
    }
}
