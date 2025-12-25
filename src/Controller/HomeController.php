<?php
declare(strict_types=1);

namespace eLonePath\Controller;

use eLonePath\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController
{
    protected View $view;

    public function __construct()
    {
        $this->view = new View();
        $this->view->setLayout('layouts/default.php');
    }

    public function index(Request $Request): Response
    {
        $this->view->setRequest($Request);

        $this->view->set([
            'title' => 'Homepage',
            'message' => 'Welcome to your Symfony application!',
            'items' => ['First', 'Second', 'Third']
        ]);

        $content = $this->view->render();

        return new Response($content);
    }
}
