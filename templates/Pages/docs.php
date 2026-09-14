<?php
declare(strict_types=1);

/**
 * @var array<array{basename: string, title: string}> $files
 * @var \App\View\AppView $this
 *
 * @link \App\Controller\PagesController::docs()
 */
?>

<article class="p-4 p-md-5">
    <?php
    /** @link templates/element/chapter_header.php */
    echo $this->element(name: 'chapter_header', data: ['title' => __('Documentation')]);
    ?>

    <?php if ($files) : ?>
    <ul>
        <?php foreach ($files as $file) : ?>
        <li>
            <?= $this->Html->link(
                text: $file['title'],
                url: ['controller' => 'Pages', 'action' => 'doc', $file['basename']],
            ) ?>
        </li>
        <?php endforeach ?>
    </ul>
    <?php endif; ?>
</article>
