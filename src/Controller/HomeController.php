<?php
declare(strict_types=1);

namespace eLonePath\Controller;

use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $this->set([
            'title' => 'Homepage',
            'message' => 'Welcome to your Symfony application!',
            'items' => ['First', 'Second', 'Third']
        ]);

        return $this->render();
    }
}
