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
    <li>
        <a
            href="<?= $this->Html->url(['controller' => 'Story', 'action' => 'chapter', $game->gameId, 1]) ?>"
            class="d-block p-3 text-decoration-none"
        >
            <div class="fs-3">
                <?= $game->title ?>
            </div>

            <div class="fs-5 mt-1 text-body-secondary">
                <?= $game->author ?>
            </div>

            <?php if ($game->translators) : ?>
            <div class="fs-5 mt-1 text-body-secondary">
                Tradotto da <?= $game->translators ?>
            </div>
            <?php endif; ?>

            <?php if ($game->description) : ?>
            <div class="mt-1 fs-5">
                <?= $game->description ?>
            </div>
            <?php endif; ?>

            <div class="mt-1">
                (lingua <?= $game->language ?>, versione <?= $game->version ?>)
            </div>
        </a>
    </li>
    <?php endforeach; ?>
</ul>
