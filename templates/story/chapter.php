<?php
declare(strict_types=1);

/**
 * @var \App\Story\Game $game
 * @var \App\Story\Node $node
 * @var \Elone\Core\View\View $this
 *
 * @link \App\Controller\StoryController::chapter()
 */
?>

<header class="mb-5 text-end">
    <h4 id="story-title" class="elone-title m-0"><?= $game->title ?></h4>

    <div id="story-page" class="story-page fs-5">Pagina <?= $node->id ?></div>
</header>

<?php if ($node->image) : ?>
    <img src="<?= $node->image ?>" class="img-fluid mx-auto mb-5 d-block" />
<?php endif ?>

<section id="story-content" class="fs-4 mb-4">
    <?= $node->content ?>
</section>

<?php if ($node->choices) : ?>
    <nav id="story-choices" class="d-flex flex-column gap-2">
        <?php foreach ($node->choices as $choice) : ?>
        <a
                href="<?= $choice->target ?>"
                class="elone-button fw-medium p-2 fs-5 text-decoration-none"
        >
            <?= $choice->content ?>
        </a>
        <?php endforeach; ?>
    </nav>
<?php endif; ?>

<?php if ($node->type === 'ending') : ?>
<div class="story-result" class="mt-5">
    <?php if ($node->victory === true) : ?>
        <div id="story-victory" class="p-3">
            <p class="fs-3 mb-4">Hai vinto!</p>

            <a href="/" class="elone-button d-inline-block px-3 py-2 text-decoration-none">
                Torna alla homepage
            </a>
        </div>
    <?php else : ?>
        <div id="story-defeat" class="p-3">
            <p class="fs-2 fst-italic mb-3">La tua vita finisce qui.</p>

            <p class="fs-3 mb-4">Hai perso!</p>

            <a
               href="<?= $this->Html->url(['controller' => 'Story', 'action' => 'chapter', $game->gameId, 1]) ?>"
               class="elone-button d-inline-block px-3 py-2 text-decoration-none">
                Ricomincia da pagina 1
            </a>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>
