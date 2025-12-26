<?php
declare(strict_types=1);

namespace eLonePath\Test\Controller;

use eLonePath\Controller\HomeController;
use eLonePath\TestCase\ControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(HomeController::class)]
class HomeControllerTest extends ControllerTestCase
{
    #[Test]
    public function testIndexReturnsSuccessfulResponse(): void
    {
        $this->executeAction('home');
        $this->assertResponseIsSuccessful();
        $this->assertResponseContains('Homepage');
        $this->assertResponseContains('Welcome to your application!');
        $this->assertResponseContains('First');
        $this->assertResponseContains('Second');
        $this->assertResponseContains('Third');
    }
}
