<?php
declare(strict_types=1);

/**
 * @var \App\View\AppView $this
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
    <em>eLone Path</em> porta l'esperienza dei librigame cartacei nel digitale, senza tradirne lo spirito: storie fatte
    di pagine numerate, scelte, prove di abilità, combattimenti e finali diversi.
</p>

<p class="fs-5 mb-4">
    Come su carta, sei libero di sfogliare come vuoi: puoi tornare indietro dopo una sconfitta, o saltare a una
    pagina qualsiasi cambiando l'indirizzo. Nessuno stato nascosto sul server: il tuo personaggio, quando c'è,
    viaggia nell'indirizzo stesso della pagina — puoi salvarla, condividerla, riprendere da lì.
</p>

<p class="fs-5 mb-2">
    Le storie che lo richiedono iniziano con la creazione di un personaggio: distribuisci venti punti tra
    <strong>Forza</strong>, <strong>Agilità</strong>, <strong>Percezione</strong> e <strong>Volontà</strong> — o
    lascia che sia il caso a deciderlo per te.
</p>

<p class="fs-5 mb-4">
    In combattimento, l'Agilità decide chi colpisce, la Forza quanto sono i danni procurati al nemico.
</p>

<div class="text-center">
    <?= $this->Html->link(
        text: 'Le storie',
        url: ['controller' => 'Pages', 'action' => 'stories'],
        options: ['class' => 'elone-button fs-4'],
    ) ?>
</div>
