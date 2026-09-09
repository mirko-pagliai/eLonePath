<?php
declare(strict_types=1);

/**
 * Common element for the various story templates, adds a header.
 *
 * @var \App\View\AppView $this
 * @var string $subtitle
 * @var string $title
 */

if (!$title) {
    throw new LogicException('`Title` argument is empty');
}
?>

<header class="d-flex justify-content-end mb-5">
    <div class="d-flex flex-column align-self-center text-end">
        <h2 class="fs-4 fw-bolder m-0">eLonePath</h2>

        <h3 class="ff-cinzel m-0"><?= h($title) ?></h3>

        <hr class="border-2 my-1" />

        <?php if ($subtitle) : ?>
        <div class="story-page fs-4"><?= h($subtitle) ?></div>
        <?php endif; ?>
    </div>

    <?= $this->Html->image(path: '/img/icons/icon-192.png', options: [
        'class' => 'ms-1',
        'style' => 'max-height: 70px',
    ]) ?>
</header>
