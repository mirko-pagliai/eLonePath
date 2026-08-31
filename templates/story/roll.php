<?php
declare(strict_types=1);

/**
 * @var \App\Story\Game $game
 * @var list<int> $rolls
 * @var bool $success
 * @var int $target
 * @var int $total
 * @var \Elone\Core\View\View $this
 *
 * @link \App\Controller\StoryController::roll()
 */
?>

<header class="mb-5 text-end">
    <h4 id="story-title" class="elone-title m-0"><?= htmlspecialchars($game->title) ?></h4>
</header>

<section id="dice-result" class="fs-4 mb-4 text-center">
    <p class="fs-5">
        Hai lanciato: <?= implode(', ', $rolls) ?> (totale <?= $total ?>)
    </p>

    <?php if ($success) : ?>
        <p class="fs-3 mb-4">Hai superato la prova!</p>
    <?php else : ?>
        <p class="fs-3 mb-4">Non hai superato la prova.</p>
    <?php endif; ?>

    <?= $this->Html->link(
        text: 'Continua',
        params: ['controller' => 'Story', 'action' => 'chapter', $game->gameId, $target],
        options: ['class' => 'elone-button d-inline-block px-3 py-2 text-decoration-none'],
    ) ?>
</section>
