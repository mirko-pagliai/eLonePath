<?php
declare(strict_types=1);

/**
 * Common element for the various story templates, adds a header.
 *
 * @var \Elone\Core\View\View $this
 * @var string $subtitle
 * @var string $title
 */

if (!$title) {
    throw new LogicException('`Title` argument is empty');
}
?>

<header class="d-flex justify-content-end mb-5">
    <div class="d-flex flex-column align-self-center text-end">
        <h3 id="story-title" class="m-0"><?= h($title) ?></h3>

        <?php if ($subtitle) : ?>
        <div id="story-page" class="story-page fs-4"><?= h($subtitle) ?></div>
        <?php endif; ?>
    </div>

    <?= $this->Html->image(path: '/img/icons/icon-192.png', options: [
        'class' => 'ms-1',
        'style' => 'max-height: 70px',
    ]) ?>
</header>
