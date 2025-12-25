<?php
declare(strict_types=1);

namespace eLonePath\Controller;

use eLonePath\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController
{
    private View $view;

    public function __construct()
    {
        $this->view = new View();
        $this->view->setLayout('layouts/default.php');
    }

    public function index(Request $Request): Response
    {
        $content = $this->view->render('home/index.php', [
            'title' => 'Homepage',
            'message' => 'Welcome to your Symfony application!',
            'items' => ['First', 'Second', 'Third']
        ]);

        return new Response($content);
    }
}
