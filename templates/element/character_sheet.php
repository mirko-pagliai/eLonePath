<?php
declare(strict_types=1);

/**
 * Shows the player's current character.
 *
 * @var \App\Story\Character $character
 * @var \App\View\AppView $this
 */
?>

<aside class="mb-4">
    <?= $this->Html->link(text: __('Your character'), url: '#collapseExample', options: [
        'aria-controls' => 'collapseExample',
        'aria-expanded' => 'false',
        'class' => 'elone-button px-2',
        'data-bs-toggle' => 'collapse',
        'icon' => 'bi-person-fill fs-5',
        'role' => 'button',
    ]) ?>

    <div id="collapseExample" class="collapse fs-3 mt-4 mb-2 pb-3" style="border-bottom: 2px solid var(--elone-accent)">
        <ul class="list-unstyled mb-0 row">
            <li class="col-6 col-md-3">
                <?= __('Strength: {0}', "<strong>$character->strength</strong>") ?>
            </li>
            <li class="col-6 col-md-3">
                <?= __('Agility: {0}', "<strong>$character->agility</strong>") ?>
            </li>
            <li class="col-6 col-md-3">
                <?= __('Perception: {0}', "<strong>$character->perception</strong>") ?>
            </li>
            <li class="col-6 col-md-3">
                <?= __('Willpower: {0}', "<strong>$character->willpower</strong>") ?>
            </li>
        </ul>

        <p class="mb-0 mt-2">
            <?= __(
                'Life points: {0} / {1}',
                "<strong>$character->lifePoints</strong>",
                "<strong>$character->maxLifePoints</strong>",
            ) ?>
        </p>
    </div>
</aside>
