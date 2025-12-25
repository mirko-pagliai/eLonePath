<?php
declare(strict_types=1);

namespace eLonePath\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->set([
            'title' => 'Homepage',
            'message' => 'Welcome to your Symfony application!',
            'items' => ['First', 'Second', 'Third']
        ]);
    }
}
