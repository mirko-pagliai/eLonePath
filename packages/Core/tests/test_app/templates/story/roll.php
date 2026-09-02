<?php
declare(strict_types=1);

/**
 * @var \App\Story\Game $game
 * @var list<int> $rolls
 * @var bool $success
 * @var int $target
 * @var int $total
 * @var \App\View\AppView $this
 *
 * @link \App\Controller\StoryController::roll()
 */
?>

<header class="mb-5 text-end">
    <?= $this->element('story-title', ['game' => $game]) ?>
</header>

<section id="dice-result" class="fs-4 mb-4 text-center">
    <p class="fs-1 mb-2">
        <?php foreach ($rolls as $roll) : ?>
            <?= $this->Html->icon("dice-$roll", ['class' => 'mx-1']) ?>
        <?php endforeach; ?>
    </p>

    <p class="fs-5">
        Totale: <?= $total ?>
    </p>

    <?php if ($success) : ?>
        <p class="fs-3 mb-4">Hai superato la prova!</p>
    <?php else : ?>
        <p class="fs-3 mb-4">Non hai superato la prova.</p>
    <?php endif; ?>

    <?= $this->Html->link(
        text: 'Continua',
        url: ['controller' => 'Story', 'action' => 'chapter', $game->gameId, $target],
        options: ['class' => 'elone-button d-inline-block px-3 py-2 text-decoration-none'],
    ) ?>
</section>
