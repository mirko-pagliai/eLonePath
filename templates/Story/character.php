<?php
declare(strict_types=1);

/**
 * @var \App\Story\Game $game
 * @var string|null $error
 * @var \App\View\AppView $this
 *
 * @link \App\Controller\StoryController::character()
 */

/** @link templates/element/chapter_header.php */
echo $this->element(name: 'chapter_header', data: ['title' => $game->title, 'subtitle' => 'Crea il tuo personaggio']);

if ($error) {
    echo $this->Alert->render('danger', $error);
}
?>

<p class="mb-4">
    Distribuisci un <strong>totale di 20 punti</strong> tra i quattro attributi.
</p>

<p class="mb-4">
    <strong>Forza</strong> e <strong>Agilità</strong> vanno da 4 a 10 ciascuna.<br />
    <strong>Percezione</strong> e <strong>Volontà</strong> vanno da 1 a 5 ciascuna.<br />
    La somma dei quattro deve fare esattamente 20.
</p>

<?php
echo $this->Form->create(['controller' => 'Story', 'action' => 'character', $game->gameId]);
echo $this->Form->control('strength', 'Forza', ['type' => 'number', 'min' => 4, 'max' => 10, 'required' => true]);
echo $this->Form->control('agility', 'Agilità', ['type' => 'number', 'min' => 4, 'max' => 10, 'required' => true]);
echo $this->Form->control('perception', 'Percezione', ['type' => 'number', 'min' => 1, 'max' => 5, 'required' => true]);
echo $this->Form->control('willpower', 'Volontà', ['type' => 'number', 'min' => 1, 'max' => 5, 'required' => true]);
echo $this->Form->submit('Crea personaggio', ['class' => 'elone-button fs-4']);
echo $this->Form->end();
?>

<hr class="border-3 my-4" />

<div class="fs-4">
    <p class="fst-italic">Oppure...</p>

    <p>Non hai voglia di fare i calcoli?</p>

    <?= $this->Html->link(
        text: 'Crea personaggio casuale',
        url: ['controller' => 'Story', 'action' => 'random', $game->gameId],
        options: ['class' => 'elone-button'],
    ) ?>
</div>
