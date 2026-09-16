<?php
declare(strict_types=1);

/**
 * @var string $content
 * @var string $title
 * @var \App\View\AppView $this
 *
 * @link \App\Controller\PagesController::doc()
 */
?>

<article class="min-vh-100 p-4 p-lg-5">
    <?php
    /** @link templates/element/chapter_header.php */
    echo $this->element(name: 'chapter_header', data: ['title' => __('Documentation')]);
    ?>

    <?= $this->Html->markdown($content) ?>
</article>
