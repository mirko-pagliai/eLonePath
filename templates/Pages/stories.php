<?php
declare(strict_types=1);

/**
 * @var array<\App\Story\Game> $stories
 * @var \App\View\AppView $this
 *
 * @link \App\Controller\PagesController::stories()
 */
?>

<article class="min-vh-100 p-4 p-lg-5">
    <h1 class="display-5 mb-4 text-center">
        eLone Path
    </h1>

    <ul>
        <?php foreach ($stories as $game) : ?>
        <li class="position-relative py-3 px-1">
            <?= $this->Html->link(
                text: $game->title,
                url: ['controller' => 'Story', 'action' => 'character', $game->gameId],
                options: ['class' => 'd-block fs-2 stretched-link text-decoration-none'],
            ) ?>

            <?php if ($game->description) : ?>
            <div class="fs-4">
                <?= h($game->description) ?>
            </div>
            <?php endif; ?>

            <div class="d-flex flex-row gap-4 fs-5">
                <div>
                    <?= __('Genre: {0}', lcfirst($game->genre->label())) ?>
                </div>

                <div>
                    <?= __('Difficulty: {0}', lcfirst($game->difficulty->label())) ?>
                </div>

                <?php if ($game->requiresCharacter) : ?>
                <div>
                    <?= __('Requires character creation') ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="fs-5 text-body-secondary">
                <?= __('Authors: {0}', h($game->author)) ?>
            </div>

            <?php if ($game->translators) : ?>
            <div class="fs-5 text-body-secondary">
                <?= __('Translators: {0}', h($game->translators)) ?>
            </div>
            <?php endif; ?>

            <div class="fs-5">
                <?= __('Language {0}, version {1}', h($game->language), h($game->version)) ?>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
</article>
