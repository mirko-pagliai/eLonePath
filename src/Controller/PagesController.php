<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Controller;

final class PagesController extends Controller
{
    public function view(int $id): void
    {
        $this->set(compact('id'));
    }
}
