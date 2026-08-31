<?php
declare(strict_types=1);

/**
 * @var array<\App\Story\Game> $stories
 * @var \Elone\Core\View\View $this
 *
 * @link \App\Controller\PagesController::stories()
 */
?>

<h1 class="display-3 ff-cinzel fw-semibold mb-3 text-center">
    Elone Path
</h1>

<ul id="stories-list" class="list-unstyled">
    <?php foreach ($stories as $game) : ?>
        <li class="position-relative p-3">
            <?= $this->Html->link(
                text: $game->title,
                url: ['controller' => 'Story', 'action' => 'chapter', $game->gameId, 1],
                options: ['class' => 'd-block fs-3 stretched-link text-decoration-none'],
            ) ?>

            <div class="fs-5 mt-1 text-body-secondary">
                <?= h($game->author) ?>
            </div>

            <?php if ($game->translators) : ?>
                <div class="fs-5 mt-1 text-body-secondary">
                    Tradotto da <?= h($game->translators) ?>
                </div>
            <?php endif; ?>

            <?php if ($game->description) : ?>
                <div class="mt-1 fs-5">
                    <?= h($game->description) ?>
                </div>
            <?php endif; ?>

            <div class="mt-1">
                (lingua <?= h($game->language) ?>, versione <?= h($game->version) ?>)
            </div>
        </li>
    <?php endforeach; ?>
</ul>
