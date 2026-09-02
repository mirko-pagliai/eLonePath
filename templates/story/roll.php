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

<header class="d-flex justify-content-end mb-5">
    <div class="d-flex flex-column align-self-end text-end">
        <h3 id="story-title" class="m-0"><?= h($game->title) ?></h3>

        <div id="story-page" class="story-page fs-4">Lancio dei dadi</div>
    </div>

    <div class="ms-1">
        <?= $this->Html->image(
            path: '/img/icons/icon-192.png',
            options: ['style' => 'max-height: 70px'],
        ) ?>
    </div>
</header>

<section id="dice-result" class="fs-4 mb-4 text-center">
    <p class="mb-2">
        <?php foreach ($rolls as $roll) : ?>
            <?= $this->Html->icon(
                name: "dice-$roll",
                options: ['class' => 'mx-1', 'style' => 'font-size: 5rem'],
            ) ?>
        <?php endforeach; ?>
    </p>

    <p class="fs-4">
        Totale: <strong><?= $total ?></strong>
    </p>

    <p class="fs-3 mb-4">
    <?php if ($success) : ?>
        Hai superato la prova!
    <?php else : ?>
        Non hai superato la prova.
    <?php endif; ?>
    </p>

    <?= $this->Html->link(
        text: 'Continua',
        url: ['controller' => 'Story', 'action' => 'chapter', $game->gameId, $target],
        options: ['class' => 'elone-button d-inline-block px-3 py-2 text-decoration-none'],
    ) ?>
</section>
