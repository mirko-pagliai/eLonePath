<?php
declare(strict_types=1);

/**
 * @var \Elone\Core\View\View $this
 *
 * @link \App\Controller\PagesController::home()
 */
?>

<?= $this->Html->image(
    path: '/img/logo-600.png',
    options: ['class' => 'd-block img-fluid mb-5 mx-auto'],
) ?>

<p class="fs-4 mb-4 text-center">
    Librogame digitali
</p>

<div class="text-center">
    <?= $this->Html->link(
        text: 'Le storie',
        url: ['controller' => 'Pages', 'action' => 'stories'],
        options: ['class' => 'btn fs-4 elone-button px-4 py-2'],
    ) ?>
</div>
