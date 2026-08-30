<?php
declare(strict_types=1);

/**
 * @var \Elone\Core\View\View $this
 *
 * @link \App\Controller\PagesController::home()
 */
?>

<h1 class="display-3 ff-cinzel fw-semibold mb-3 text-center">
    Elone Path
</h1>

<p class="fs-4 mb-4 text-center">
    Librogame digitali
</p>

<div class="text-center">
    <?= $this->Html->link(
        text: 'Le storie',
        route: ['controller' => 'Pages', 'action' => 'stories'],
        attributes: ['class' => 'btn fs-4 elone-button px-4 py-2'],
    ) ?>
</div>
