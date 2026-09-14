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
        <div class="d-flex flex-wrap gap-2 justify-content-between">
            <div>
                <?= __('Strength: {0}', "<strong>$character->strength</strong>") ?>
            </div>

            <div>
                <?= __('Agility: {0}', "<strong>$character->agility</strong>") ?>
            </div>

            <div>
                <?= __('Perception: {0}', "<strong>$character->perception</strong>") ?>
            </div>

            <div>
                <?= __('Willpower: {0}', "<strong>$character->willpower</strong>") ?>
            </div>

            <div>
                <?= __(
                    'Life points: {0} / {1}',
                    "<strong>$character->lifePoints</strong>",
                    "<strong>$character->maxLifePoints</strong>",
                ) ?>
            </div>
        </div>
    </div>
</aside>
