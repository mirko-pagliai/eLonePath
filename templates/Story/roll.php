<?php
declare(strict_types=1);

/**
 * @var \App\Story\Character|null $character
 * @var \App\Story\Game $game
 * @var list<int> $rolls
 * @var bool $success
 * @var int $target
 * @var int $total
 * @var bool $perceptionBypassed
 * @var \App\View\AppView $this
 *
 * @link \App\Controller\StoryController::roll()
 */
?>

<article class="p-4 pt-5 p-xl-5">
    <?php
    /** @link templates/element/chapter_header.php */
    echo $this->element(name: 'chapter_header', data: ['title' => $game->title, 'subtitle' => 'Lancio dei dadi']);

    if ($character !== null) {
        /** @link templates/element/character_sheet.php */
        echo $this->element(name: 'character_sheet', data: ['character' => $character]);
    }
    ?>

    <section id="dice-result" class="fs-4 mb-4 text-center">
        <?php if ($perceptionBypassed) : ?>
        <p class="fs-2 fst-italic mb-4">
            La tua <em>oercezione</em> ti permette di individuare il pericolo prima ancora di correrlo.
        </p>
        <?php else : ?>
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
        <?php endif; ?>

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
            options: ['class' => 'elone-button'],
        ) ?>
    </section>
</article>
