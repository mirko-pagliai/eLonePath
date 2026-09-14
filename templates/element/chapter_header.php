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

<header class="mb-5 d-flex justify-content-end">
    <div class="text-end">
        <div class="d-flex flex-row">
            <div>
                <h2 class="fs-4 fw-bolder m-0">eLonePath</h2>

                <h4 class="ff-cinzel m-0">
                    <?= h($title) ?>
                </h4>
            </div>

            <?= $this->Html->image(path: '/img/icons/icon-192.png', options: [
                'class' => 'ms-1',
                'style' => 'max-height: 62px',
            ]) ?>
        </div>

        <?php if ($subtitle) : ?>
        <hr class="border-2 my-2 "/>

        <div style="color: var(--elone-muted);">
            <?= h($subtitle) ?>
        </div>
        <?php endif; ?>
    </div>
</header>
