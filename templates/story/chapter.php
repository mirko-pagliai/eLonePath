<?php
declare(strict_types=1);

/**
 * @var object $game
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
    <img src="<?= $node->image ?>" class="img-fluid mx-auto mb-4 d-block" />
<?php endif ?>

<section id="story-content" class="fs-4 mb-4">
    <?= $node->content ?>
</section>


<nav id="story-choices" class="d-flex flex-column gap-2">
    <?php foreach ($node->choices as $choice) : ?>
    <a href="<?= $choice->target ?>" class="elone-button text-decoration-none"><?= $choice->content ?></a>
    <?php endforeach; ?>
</nav>
