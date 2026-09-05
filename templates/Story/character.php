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
    <div class="alert alert-danger" role="alert">
        <?= h($error) ?>
    </div>
<?php endif; ?>

<p class="fs-5 mb-4">
    Distribuisci <strong>20 punti</strong> tra i quattro attributi. Forza e Agilità non hanno un massimo proprio;
    Percezione e Volontà vanno da 1 a 5 ciascuna. La somma dei quattro deve fare esattamente 20.
</p>

<form
    method="post"
    action="<?= h($this->Html->url(['controller' => 'Story', 'action' => 'character', $game->gameId])) ?>"
>
    <div class="mb-3">
        <label for="strength" class="form-label">Forza</label>
        <input type="number" class="form-control" id="strength" name="strength" min="1" required>
    </div>

    <div class="mb-3">
        <label for="agility" class="form-label">Agilità</label>
        <input type="number" class="form-control" id="agility" name="agility" min="1" required>
    </div>

    <div class="mb-3">
        <label for="perception" class="form-label">Percezione</label>
        <input type="number" class="form-control" id="perception" name="perception" min="1" max="5" required>
    </div>

    <div class="mb-3">
        <label for="willpower" class="form-label">Volontà</label>
        <input type="number" class="form-control" id="willpower" name="willpower" min="1" max="5" required>
    </div>

    <button type="submit" class="btn fs-4 elone-button px-4 py-2">Crea personaggio</button>
</form>
