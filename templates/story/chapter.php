<?php
declare(strict_types=1);

/**
 * @var object $choices
 * @var string $content
 * @var object $game
 * @var string|null $image
 * @var int $page
 * @var \Elone\Core\View\View $this
 *
 * @link \App\Controller\StoryController::chapter()
 */

use Michelf\Markdown;
?>

<header class="mb-5 text-end">
    <h4 id="story-title" class="elone-title m-0"><?= $game->title ?></h4>

    <div id="story-page" class="story-page fs-5">Pagina <?= $page ?></div>
</header>

<?php if ($image) : ?>
    <img src="<?= $image ?>" class="img-fluid mx-auto mb-4 d-block" />
<?php endif ?>

<section id="story-content" class="fs-4 mb-4">
    <?= Markdown::defaultTransform($content) ?>
</section>


<nav id="story-choices" class="d-flex flex-column gap-2">
    <?php foreach ($choices as $choice) : ?>
    <a href="<?= $choice->target ?>" class="elone-button text-decoration-none"><?= $choice->text ?></a>
    <?php endforeach; ?>
</nav>
