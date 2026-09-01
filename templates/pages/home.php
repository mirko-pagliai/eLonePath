<?php
declare(strict_types=1);

/**
 * @var \Elone\Core\View\View $this
 *
 * @link \App\Controller\PagesController::home()
 */
?>

<?= $this->Html->image(
    path: '/img/logo-600.png',
    options: ['class' => 'd-block img-fluid mb-5 mx-auto'],
) ?>

<p class="fs-4 mb-4 text-center">
    Librogame digitali
</p>

<p class="fs-5 mb-2">
    <em>Elone Path</em> porta l'esperienza dei librigame cartacei nel digitale, senza tradirne lo spirito: storie fatte
    di pagine numerate, scelte, prove di abilità e finali diversi.
</p>

<p class="fs-5 mb-4">
    Come su carta, sei libero di sfogliare come vuoi: puoi tornare indietro dopo una sconfitta, o saltare a una
    pagina qualsiasi cambiando l'indirizzo. Nessuno stato nascosto tiene traccia di cosa hai fatto — quello che
    vedi dipende solo dalla pagina in cui ti trovi.
</p>

<div class="text-center">
    <?= $this->Html->link(
        text: 'Le storie',
        url: ['controller' => 'Pages', 'action' => 'stories'],
        options: ['class' => 'btn fs-4 elone-button px-4 py-2'],
    ) ?>
</div>
