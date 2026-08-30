<?php
declare(strict_types=1);

/**
 * @var array<object> $stories
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
            href="<?= $this->Html->url(['controller' => 'Story', 'action' => 'chapter', $game->id, 1]) ?>"
            class="d-block p-3 text-decoration-none"
        >
            <div class="story-item-title fs-3">
                <?= $game->title ?>
            </div>

            <div class="story-item-author text-body-secondary mt-1">
                <?= $game->author ?>
            </div>

            <?php if ($game->description) : ?>
            <div class="story-item-description mt-1">
                <?= $game->description ?>
            </div>
            <?php endif; ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>
