<?php
declare(strict_types=1);

/**
 * @var \App\Story\Game $game
 * @var \Elone\Core\View\View $this
 *
 * @link \App\Controller\StoryController::start()
 */
?>

<?= $this->element(name: 'chapter_header', data: ['title' => h($game->title), 'subtitle' => 'Introduzione']) ?>

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
