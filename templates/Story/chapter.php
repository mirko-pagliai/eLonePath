<?php
declare(strict_types=1);

/**
 * @var \App\Story\Character|null $character
 * @var \App\Story\Game $game
 * @var \App\Story\Nodes\Node $node
 * @var \App\View\AppView $this
 *
 * @link \App\Controller\StoryController::chapter()
 */

use App\Story\Nodes\CombatNode;
use App\Story\Nodes\DefeatNode;
use App\Story\Nodes\DiceNode;
use App\Story\Nodes\PassageNode;
use App\Story\Nodes\VictoryNode;

/** @link templates/element/chapter_header.php */
echo $this->element(name: 'chapter_header', data: ['title' => $game->title, 'subtitle' => "Pagina $node->id"]);

if ($character) {
    /** @link templates/element/character_sheet.php */
    echo $this->element(name: 'character_sheet', data: compact('character'));
}
?>

<section id="story-content" class="fs-4 mb-4">
    <?= $this->Html->markdown(markdown: $node->content) ?>
</section>

<?php if ($node instanceof PassageNode) : ?>
    <nav id="story-choices" class="d-flex flex-column gap-3">
        <?php
        foreach ($node->choices as $choice) {
            echo $this->Story->link(
                text: $choice->content . ' <i class="bi bi-arrow-right"></i>',
                url: ['controller' => 'Story', 'action' => 'chapter', $game->gameId, $choice->target],
                options: [
                    'class' => 'elone-button fw-medium fs-5',
                    'escape' => false,
                ],
            );
        }
        ?>
    </nav>
<?php endif; ?>

<?php if ($node instanceof DiceNode) : ?>
    <div id="story-dice" class="mt-4 text-center">
        <p class="fs-3">
            Lancia <strong><?= $node->requiredRolls ?> <?= $node->requiredRolls === 1 ? 'dado' : 'dadi' ?></strong>:
            serve almeno un <strong>minimo di <?= $node->minimum ?></strong> per superare la prova.
        </p>

        <?php
        if ($node->requiredRolls === 1) {
            $text = 'Lancia <strong>1</strong> dado';
        } else {
            $text = "Lancia <strong>$node->requiredRolls</strong> dadi";
        }

        echo $this->Story->link(
            text: $this->Html->icon('dice-6', ['class' => 'me-1']) . " $text",
            url: ['controller' => 'Story', 'action' => 'roll', $game->gameId, $node->id],
            options: [
                'class' => 'elone-button',
                'escape' => false,
            ],
        );
        ?>
    </div>
<?php endif; ?>

<?php if ($node instanceof CombatNode) : ?>
    <div id="story-combat-intro" class="mt-4 text-center">
        <p class="fs-3">
            Un nemico ti sbarra la strada: <strong><?= h($node->enemyName) ?></strong>
            (<?= $node->enemyMaxLifePoints ?> punti vita).
        </p>

        <?= $this->Story->link(
            text: $this->Html->icon('bi-crosshair', ['class' => 'me-1']) . ' Affronta il nemico',
            url: ['controller' => 'Story', 'action' => 'combat', $game->gameId, $node->id],
            options: [
                'class' => 'elone-button',
                'escape' => false,
            ],
        ) ?>
    </div>
<?php endif; ?>

<?php if ($node instanceof VictoryNode) : ?>
    <div id="story-victory" class="mt-5 p-3">
        <p class="fs-2 fst-italic mb-3">La tua impresa è riuscita!</p>

        <p class="fs-3 mb-4">Hai vinto.</p>

        <?= $this->Html->link(
            text: 'Torna alla homepage',
            url: '/',
            options: ['class' => 'elone-button'],
        ) ?>
    </div>
<?php elseif ($node instanceof DefeatNode) : ?>
    <div id="story-defeat" class="mt-5 p-3">
        <p class="fs-2 fst-italic mb-3">La tua vita finisce qui!</p>

        <p class="fs-3 mb-4">Hai perso.</p>

        <?= $this->Html->link(
            text: "Ricomincia dall'inizio",
            url: ['controller' => 'Story', 'action' => 'chapter', $game->gameId, 1],
            options: [
                'class' => 'elone-button',
            ],
        ) ?>
    </div>
<?php endif; ?>
