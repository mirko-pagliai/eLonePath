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
?>

<?php if ($error !== null) : ?>
    <?= $this->Alert->render('danger', $error) ?>
<?php endif; ?>

<p class="fs-5 mb-4">
    Distribuisci un <strong>totale di 20 punti</strong> tra i quattro attributi.
</p>

<p class="fs-5 mb-4">
    <strong>Forza</strong> e <strong>Agilità</strong> non hanno un massimo proprio.<br />
    <strong>Percezione</strong> e <strong>Volontà</strong> vanno da 1 a 5 ciascuna.<br />
    La somma dei quattro deve fare esattamente 20.
</p>

<?= $this->Form->create(['controller' => 'Story', 'action' => 'character', $game->gameId]) ?>

<?= $this->Form->input('strength', 'Forza', ['type' => 'number', 'min' => 1, 'required' => true]) ?>
<?= $this->Form->input('agility', 'Agilità', ['type' => 'number', 'min' => 1, 'required' => true]) ?>
<?= $this->Form->input('perception', 'Percezione', ['type' => 'number', 'min' => 1, 'max' => 5, 'required' => true]) ?>
<?= $this->Form->input('willpower', 'Volontà', ['type' => 'number', 'min' => 1, 'max' => 5, 'required' => true]) ?>

<button type="submit" class="btn fs-4 elone-button px-4 py-2">Crea personaggio</button>

<?= $this->Form->end() ?>

<p class="fs-5 mt-3">
    Non hai voglia di fare i calcoli?
    <?= $this->Html->link(
        text: 'Crealo casualmente',
        url: ['controller' => 'Story', 'action' => 'random', $game->gameId],
    ) ?>
</p>
