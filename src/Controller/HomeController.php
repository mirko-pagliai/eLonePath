<?php
declare(strict_types=1);

namespace eLonePath\Controller;

use eLonePath\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Home controller for the main page
 */
class HomeController
{
    private View $view;

    public function __construct()
    {
        $this->view = new View();
    }

    /**
     * Display the home page
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->view->render('home/index', [
            'title' => 'eLonePath - Home',
        ]);
    }
}
