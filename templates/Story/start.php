<?php
declare(strict_types=1);

/**
 * @var \App\Story\Game $game
 * @var \App\View\AppView $this
 *
 * @link \App\Controller\StoryController::start()
 */

/** @link templates/element/chapter_header.php */
echo $this->element(name: 'chapter_header', data: ['title' => $game->title, 'subtitle' => 'Introduzione']);
?>

<section id="story-content" class="fs-4 mb-4">
    <?= $this->Html->markdown(markdown: $game->preface) ?>
</section>

<div class="text-center">
    <?= $this->Story->link(
        text: 'Comincia la partita',
        url: ['controller' => 'Story', 'action' => 'chapter', $game->gameId, 1],
        options: ['class' => 'elone-button fs-4'],
    ) ?>
</div>
