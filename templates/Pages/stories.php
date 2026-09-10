<?php
declare(strict_types=1);

/**
 * @var array<\App\Story\Game> $stories
 * @var \App\View\AppView $this
 *
 * @link \App\Controller\PagesController::stories()
 */
?>

<h1 class="display-5 mb-4 text-center">
    eLone Path
</h1>

<ul id="stories-list" class="list-unstyled">
    <?php foreach ($stories as $game) : ?>
        <li class="position-relative py-4 px-3">
            <?= $this->Html->link(
                text: $game->title,
                url: ['controller' => 'Story', 'action' => 'character', $game->gameId],
                options: ['class' => 'd-block fs-2 stretched-link text-decoration-none'],
            ) ?>

            <?php if ($game->description) : ?>
            <div class="mt-1 fs-4">
                <?= h($game->description) ?>
            </div>
            <?php endif; ?>

            <div class="d-flex flex-row gap-4 fs-5">
                <div>
                    Genere: <?= lcfirst($game->genre->label()) ?>
                </div>

                <div>
                    Difficoltà: <?= lcfirst($game->difficulty->label()) ?>
                </div>

                <?php if ($game->requiresCharacter) : ?>
                <div>
                    Richiede la creazione di un personaggio
                </div>
                <?php endif; ?>
            </div>

            <div class="fs-5 text-body-secondary">
                Autori: <?= h($game->author) ?>
            </div>

            <?php if ($game->translators) : ?>
                <div class="fs-5 text-body-secondary">
                    Traduttori: <?= h($game->translators) ?>
                </div>
            <?php endif; ?>

            <div class="fs-5">
                (lingua <?= h($game->language) ?>, versione <?= h($game->version) ?>)
            </div>
        </li>
    <?php endforeach; ?>
</ul>
