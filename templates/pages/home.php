<?php
declare(strict_types=1);

/**
 * @var \Elone\Core\View\View $this
 *
 * @link \App\Controller\PagesController::home()
 */
?>

<article class="elone-page mx-auto p-4 p-md-5">

    <header class="text-center py-5">
        <h1 class="elone-title display-3 fw-semibold mb-3">
            Elone Path
        </h1>

        <p class="fs-4 mb-4">
            Librogame digitali
        </p>

        <?php
        echo $this->Html->link('Le storie', ['controller' => 'Pages', 'action' => 'stories'], ['class' => 'btn elone-button px-4 py-2']);
        ?>
    </header>

</article>