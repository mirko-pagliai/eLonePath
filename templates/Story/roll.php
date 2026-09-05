<?php
declare(strict_types=1);

/**
 * @var \App\Story\Character|null $character
 * @var \App\Story\Game $game
 * @var list<int> $rolls
 * @var bool $success
 * @var int $target
 * @var int $total
 * @var \App\View\AppView $this
 *
 * @link \App\Controller\StoryController::roll()
 */

/** @link templates/element/chapter_header.php */
echo $this->element(name: 'chapter_header', data: ['title' => $game->title, 'subtitle' => 'Lancio dei dadi']);

if ($character !== null) {
    /** @link templates/element/character_sheet.php */
    echo $this->element(name: 'character_sheet', data: ['character' => $character]);
}
?>

<section id="dice-result" class="fs-4 mb-4 text-center">
    <p class="mb-2">
        <?php foreach ($rolls as $roll) : ?>
            <?= $this->Html->icon(
                name: "dice-$roll",
                options: ['class' => 'mx-1', 'style' => 'font-size: 5rem'],
            ) ?>
        <?php endforeach; ?>
    </p>

    <p class="fs-2">
        Totale: <strong><?= $total ?></strong>
    </p>

    <p class="fs-2 fst-italic mb-4">
    <?php if ($success) : ?>
        Hai superato la prova!
    <?php else : ?>
        Non hai superato la prova.
    <?php endif; ?>
    </p>

    <?= $this->Story->link(
        text: 'Continua',
        url: ['controller' => 'Story', 'action' => 'chapter', $game->gameId, $target],
        options: ['class' => 'elone-button d-inline-block px-3 py-2 text-decoration-none'],
    ) ?>
</section>
