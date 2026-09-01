<?php
declare(strict_types=1);

/**
 * @var \App\Story\Game $game
 * @var \Elone\Core\View\View $this
 *
 * @link \App\Controller\StoryController::start()
 */
?>

<header class="d-flex justify-content-end mb-5">
    <div class="d-flex flex-column align-self-end text-end">
        <h3 id="story-title" class="m-0"><?= h($game->title) ?></h3>

        <div id="story-page" class="story-page fs-4">Introduzione</div>
    </div>

    <div class="ms-1">
        <?= $this->Html->image(
            path: '/img/icons/icon-192.png',
            options: ['style' => 'max-height: 70px'],
        ) ?>
    </div>
</header>

<section id="story-content" class="fs-4 mb-4">
    <?= $this->Html->markdown(markdown: $game->preface) ?>
</section>

<div class="text-center">
    <?= $this->Html->link(
        text: 'Comincia la partita',
        url: ['controller' => 'Story', 'action' => 'chapter', $game->gameId, 1],
        options: ['class' => 'btn fs-4 elone-button px-4 py-2'],
    ) ?>
</div>
